<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Channel;

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

        Channel::create([
            'name' => $request->name,
            'type' => $request->type,
            'stream_url' => $request->stream_url,
            'logo' => $request->logo,
            'description' => $request->description,
            'is_active' => true,
        ]);

        return redirect()->route('admin.channels.index')->with('success', 'Contenido personalizado agregado exitosamente.');
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
}
