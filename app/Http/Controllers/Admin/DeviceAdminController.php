<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\User;

class DeviceAdminController extends Controller
{
    /**
     * Lista todos los dispositivos del sistema con información del usuario.
     */
    public function index(Request $request)
    {
        $devices = Device::with('user:id,name,email')
            ->orderBy('last_access', 'desc')
            ->paginate(50);

        return response()->json($devices);
    }

    /**
     * Lista los dispositivos de un usuario específico.
     */
    public function byUser($userId)
    {
        $user = User::findOrFail($userId);
        $devices = $user->devices()->orderBy('last_access', 'desc')->get();

        return response()->json([
            'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
            'devices' => $devices
        ]);
    }

    /**
     * Cierra la sesión de un dispositivo específico y revoca su token.
     */
    public function closeSession($deviceId)
    {
        $device = Device::findOrFail($deviceId);
        $user   = $device->user;

        // Revocar tokens Sanctum de este dispositivo
        $user->tokens()->where('name', 'device_' . $device->id)->each(fn($t) => $t->delete());
        $device->sessions()->delete();
        $device->update(['is_active' => false]);

        return response()->json(['message' => 'Sesión del dispositivo cerrada correctamente']);
    }

    /**
     * Bloquea un dispositivo marcándolo como inactivo permanentemente.
     */
    public function block($deviceId)
    {
        $device = Device::findOrFail($deviceId);
        $device->update(['is_active' => false]);
        $device->sessions()->delete();
        $device->user->tokens()->where('name', 'device_' . $device->id)->each(fn($t) => $t->delete());

        return response()->json(['message' => 'Dispositivo bloqueado']);
    }

    /**
     * Cierra TODAS las sesiones de todos los dispositivos de un usuario.
     */
    public function closeAllUserSessions($userId)
    {
        $user = User::findOrFail($userId);
        $user->tokens()->delete();
        $user->devices()->update(['is_active' => false]);
        $user->deviceSessions()->delete();

        return response()->json(['message' => 'Todas las sesiones del usuario han sido cerradas']);
    }
}
