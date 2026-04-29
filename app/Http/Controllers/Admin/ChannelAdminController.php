<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Channel;
use Illuminate\Support\Facades\Schema;

class ChannelAdminController extends Controller
{
    public function __construct()
    {
        // Parche automático: Si la columna is_active no existe (error viejo), la crea silenciosamente
        if (!Schema::hasColumn('channels', 'is_active')) {
            Schema::table('channels', function ($table) {
                $table->boolean('is_active')->default(true)->after('description');
            });
        }
    }

    /**
     * List all channels with pagination and filtering.
     */
    public function index(Request $request)
    {
        $query = Channel::query();

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%");
        }

        if ($request->has('type') && !empty($request->type)) {
            $query->where('type', $request->type);
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status);
        }

        $channels = $query->orderBy('name', 'asc')->paginate(50);

        return view('admin.channels.index', compact('channels'));
    }

    /**
     * Show the form to create a custom channel/movie.
     */
    public function create()
    {
        return view('admin.channels.create');
    }

    /**
     * Store a custom channel/movie.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:live,movie,series',
            'stream_url' => 'required|url',
            'logo' => 'nullable|url',
            'description' => 'nullable|string',
        ]);

        $logo = $request->logo;

        // Detección automática de miniatura de YouTube si no se proporcionó una imagen
        if (empty($logo) && (str_contains($request->stream_url, 'youtube.com/') || str_contains($request->stream_url, 'youtu.be/'))) {
            preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $request->stream_url, $match);
            if (isset($match[1])) {
                $logo = 'https://img.youtube.com/vi/' . $match[1] . '/hqdefault.jpg';
            }
        }

        try {
            Channel::create([
                'name' => $request->name,
                'type' => $request->type,
                'stream_url' => $request->stream_url,
                'logo' => $logo,
                'description' => $request->description,
                'is_active' => true,
            ]);

            return redirect()->route('admin.channels.index')->with('success', 'Contenido personalizado agregado exitosamente.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Error al guardar: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle the visibility (is_active) of a channel.
     */
    public function toggleStatus($id)
    {
        $channel = Channel::findOrFail($id);
        $channel->is_active = !$channel->is_active;
        $channel->save();

        return response()->json([
            'success' => true,
            'is_active' => $channel->is_active,
            'message' => 'Estado actualizado.'
        ]);
    }

    /**
     * Verifica y oculta automáticamente si está caído.
     */
    public function autoHide($id)
    {
        try {
            $channel = Channel::findOrFail($id);
            $url = $channel->stream_url;

            $opts = [
                "http" => [
                    "method" => "GET",
                    "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36\r\n",
                    "timeout" => 5
                ]
            ];
            $context = stream_context_create($opts);
            $headers = @get_headers($url, 1, $context);

            $isOnline = false;
            if ($headers && isset($headers[0])) {
                $isOnline = str_contains($headers[0], '200') || str_contains($headers[0], '302');
            }

            if (!$isOnline) {
                $channel->is_active = false;
                $channel->save();
            }
            
            return response()->json([
                'online' => $isOnline, 
                'is_active' => (bool)$channel->is_active,
                'status' => $headers ? $headers[0] : 'No responde'
            ]);
        } catch (\Exception $e) {
            return response()->json(['online' => false, 'is_active' => true, 'status' => 'Error: ' . $e->getMessage()]);
        }
    }
}
