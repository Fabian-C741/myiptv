<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->profiles()->count() === 0) {
            $user->profiles()->create([
                'name'   => $user->name ?: 'Principal',
                'is_kid' => false,
            ]);
        }

        return response()->json($user->profiles()->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'is_kid' => 'boolean',
            'pin'    => 'nullable|digits:4',
        ]);

        if ($request->user()->profiles()->count() >= 5) {
            return response()->json(['message' => 'Límite de perfiles alcanzado'], 403);
        }

        $profile = $request->user()->profiles()->create([
            'name'   => $request->name,
            'is_kid' => $request->is_kid ?? false,
            'pin'    => $request->pin ? Hash::make($request->pin) : null,
        ]);

        return response()->json($profile, 201);
    }

    public function verifyPin(Request $request, $id)
    {
        $request->validate(['pin' => 'required|digits:4']);
        $profile = $request->user()->profiles()->findOrFail($id);

        if (!$profile->pin || Hash::check($request->pin, $profile->pin)) {
            return response()->json(['message' => 'PIN verificado', 'success' => true]);
        }

        return response()->json(['message' => 'PIN incorrecto', 'success' => false], 403);
    }

    public function update(Request $request, $id)
    {
        $profile = $request->user()->profiles()->findOrFail($id);

        $data = $request->validate([
            'name'   => 'sometimes|string|max:255',
            'is_kid' => 'sometimes|boolean',
            'avatar' => 'sometimes|string|nullable',
            'pin'    => 'sometimes|digits:4|nullable',
        ]);

        if (isset($data['pin'])) {
            $data['pin'] = $data['pin'] ? Hash::make($data['pin']) : null;
        }

        $profile->update($data);

        return response()->json($profile);
    }

    /**
     * POST /profiles/{id}/avatar  — Sube foto de perfil desde la galería del dispositivo
     */
    public function uploadAvatar(Request $request, $id)
    {
        $request->validate([
            'avatar' => 'required|file|image|max:4096', // máx 4 MB
        ]);

        $profile = $request->user()->profiles()->findOrFail($id);

        // Borrar imagen anterior si es una foto local (no un avatar URL externo)
        if ($profile->avatar && str_contains($profile->avatar, '/storage/avatars/')) {
            $oldPath = 'avatars/' . basename(parse_url($profile->avatar, PHP_URL_PATH));
            Storage::disk('public')->delete($oldPath);
        }

        // Guardar nueva imagen en storage/app/public/avatars/{user_id}/
        $path = $request->file('avatar')->store(
            'avatars/' . $request->user()->id,
            'public'
        );

        $avatarUrl = config('app.url') . Storage::url($path);
        $profile->update(['avatar' => $avatarUrl]);

        return response()->json(['avatar_url' => $avatarUrl]);
    }

    public function destroy(Request $request, $id)
    {
        $profile = $request->user()->profiles()->findOrFail($id);

        if ($request->user()->profiles()->count() <= 1) {
            return response()->json(['message' => 'No puedes eliminar el único perfil'], 403);
        }

        $profile->delete();
        return response()->json(['message' => 'Perfil eliminado']);
    }
}
