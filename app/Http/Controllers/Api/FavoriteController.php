<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Favorite;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $profileId = $request->header('Profile-Id');
        
        $favorites = Favorite::with('channel')
            ->where('profile_id', $profileId)
            ->get()
            ->pluck('channel');

        return response()->json($favorites);
    }

    public function toggle(Request $request)
    {
        $request->validate(['channel_id' => 'required|exists:channels,id']);
        $profileId = $request->header('Profile-Id');
        
        // Find existing favorite
        $fav = Favorite::where('profile_id', $profileId)
                       ->where('channel_id', $request->channel_id)
                       ->first();

        if ($fav) {
            $fav->delete();
            return response()->json(['message' => 'Eliminado de favoritos', 'is_favorite' => false]);
        } else {
            Favorite::create([
                'profile_id' => $profileId,
                'channel_id' => $request->channel_id
            ]);
            return response()->json(['message' => 'Añadido a favoritos', 'is_favorite' => true]);
        }
    }
}
