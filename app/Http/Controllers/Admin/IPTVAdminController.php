<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Playlist;
use App\Models\Channel;
use App\Models\ChannelGroup;
use App\Services\XtreamService;
use App\Services\M3uParserService;
use Illuminate\Support\Facades\Log;

class IPTVAdminController extends Controller
{
    /**
     * Vista principal de gestión de contenido.
     */
    public function index()
    {
        $playlists = Playlist::withCount(['channels', 'channelGroups'])->get();
        $totalChannels = Channel::count();
        $totalMovies = Channel::where('type', 'movie')->count();
        $totalSeries = Channel::where('type', 'series')->count();

        return view('admin.content.index', compact('playlists', 'totalChannels', 'totalMovies', 'totalSeries'));
    }

    /**
     * Formulario para configurar una nueva fuente IPTV.
     */
    public function setup()
    {
        return view('admin.content.setup');
    }

    /**
     * Guarda la configuración de la lista (Xtream o M3U).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:xtream,m3u',
            'url'  => 'required|url',
            'username' => 'required_if:type,xtream',
            'password' => 'required_if:type,xtream',
        ]);

        $playlist = Playlist::create($request->all());

        return redirect()->route('admin.content')->with('success', 'Fuente IPTV agregada. Ahora puedes sincronizar el contenido.');
    }

    /**
     * Formulario para editar una fuente IPTV.
     */
    public function edit($id)
    {
        $playlist = Playlist::findOrFail($id);
        return view('admin.content.edit', compact('playlist'));
    }

    /**
     * Actualiza la configuración de la lista.
     */
    public function update(Request $request, $id)
    {
        $playlist = Playlist::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:xtream,m3u',
            'url'  => 'required|url',
            'username' => 'required_if:type,xtream',
            'password' => 'required_if:type,xtream',
        ]);

        $playlist->update($request->all());

        return redirect()->route('admin.content')->with('success', 'Fuente IPTV actualizada correctamente.');
    }

    /**
     * Elimina una fuente y todo su contenido asociado.
     */
    public function destroy($id)
    {
        $playlist = Playlist::findOrFail($id);
        
        // La base de datos debería estar configurada con cascade delete, 
        // pero lo hacemos manual para asegurar limpieza de canales/grupos.
        $playlist->channels()->delete();
        $playlist->channelGroups()->delete();
        $playlist->delete();

        return redirect()->route('admin.content')->with('success', 'Fuente y contenido eliminados permanentemente.');
    }

    /**
     * Inicia el proceso de sincronización.
     */
    public function sync($id, XtreamService $xtreamService, M3uParserService $m3uService)
    {
        $playlist = Playlist::findOrFail($id);
        
        try {
            if ($playlist->type === 'xtream') {
                $xtreamService->sync($playlist);
            } else {
                $m3uService->parseAndStore($playlist);
            }

            return redirect()->back()->with('success', 'Sincronización completada exitosamente.');
        } catch (\Exception $e) {
            Log::error("Error sincronizando IPTV: " . $e->getMessage());
            return redirect()->back()->with('error', 'Hubo un error al sincronizar: ' . $e->getMessage());
        }
    }
}
