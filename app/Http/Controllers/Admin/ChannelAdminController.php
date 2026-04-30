<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Channel;
use Illuminate\Support\Facades\Schema;

class ChannelAdminController extends Controller
{
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

        $channels = $query->orderBy('created_at', 'desc')->paginate(50);

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
        // Guardado ultra-directo para bypassear errores de servidor
        $channel = new Channel();
        $channel->name = $request->name;
        $channel->type = $request->type;
        $channel->stream_url = $request->stream_url;
        $channel->logo = $request->logo;
        $channel->backdrop = $request->backdrop;
        $channel->description = $request->description;
        $channel->release_date = $request->release_date;
        $channel->rating = $request->rating;
        $channel->duration = $request->duration;
        $channel->tmdb_id = $request->tmdb_id;
        $channel->is_adult = $request->has('is_adult');
        $channel->is_active = true;
        $channel->save();

        return redirect()->route('admin.channels.index')->with('success', 'Contenido agregado y publicado exitosamente.');
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
                $isOnline = strpos($headers[0], '200') !== false || strpos($headers[0], '302') !== false;
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

    /**
     * Verifica y oculta automáticamente todos los canales caídos.
     */
    public function bulkCheck()
    {
        $channels = Channel::where('is_active', true)->get();
        $results = [
            'checked' => 0,
            'hidden' => 0,
            'errors' => 0
        ];

        foreach ($channels as $channel) {
            try {
                $results['checked']++;
                $url = $channel->stream_url;
                
                // Si es YouTube, asumimos que está bien por ahora (o podrías validarlo)
                if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
                    continue;
                }

                $opts = [
                    "http" => [
                        "method" => "GET",
                        "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36\r\n",
                        "timeout" => 3
                    ]
                ];
                $context = stream_context_create($opts);
                $headers = @get_headers($url, 1, $context);

                $isOnline = false;
                if ($headers && isset($headers[0])) {
                    $isOnline = strpos($headers[0], '200') !== false || strpos($headers[0], '302') !== false;
                }

                if (!$isOnline) {
                    $channel->is_active = false;
                    $channel->save();
                    $results['hidden']++;
                }
            } catch (\Exception $e) {
                $results['errors']++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Limpieza completada. Se revisaron {$results['checked']} canales y se ocultaron {$results['hidden']} caídos.",
            'results' => $results
        ]);
    }
}
