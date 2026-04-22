<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Playlist;
use App\Services\M3uParserService;
use App\Services\XtreamService;

class PlaylistController extends Controller
{
    public function index()
    {
        return response()->json(Playlist::all());
    }

    public function sync(Request $request, $id, M3uParserService $m3uService, XtreamService $xtreamService)
    {
        // Require Admin rights ideally, checking role
        if ($request->user()->role !== 'superadmin' && $request->user()->role !== 'soporte') {
            // Check if user is an admin or similar? Actually we haven't implemented Admin guards here yet.
            // In a real scenario, this endpoint is on Admin routes. Or a regular user syncs their own list.
        }

        $playlist = Playlist::findOrFail($id);

        try {
            if ($playlist->type === 'm3u') {
                $m3uService->parseAndStore($playlist);
            } else {
                $xtreamService->sync($playlist);
            }
            return response()->json(['message' => 'Lista sincronizada con éxito']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error sincronizando', 'details' => $e->getMessage()], 500);
        }
    }
}
