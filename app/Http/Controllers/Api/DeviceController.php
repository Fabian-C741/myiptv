<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;

class DeviceController extends Controller
{
    /**
     * Lista los dispositivos vinculados al usuario autenticado.
     */
    public function index(Request $request)
    {
        $devices = $request->user()
            ->devices()
            ->orderBy('last_access', 'desc')
            ->get(['id', 'device_name', 'device_type', 'ip_address', 'country', 'city', 'last_access', 'is_active']);

        return response()->json($devices);
    }

    /**
     * Cierra sesión de un dispositivo específico (sin borrar el registro).
     * El usuario puede volver a iniciar sesión desde ese dispositivo.
     */
    public function destroy(Request $request, $id)
    {
        $device = $request->user()->devices()->findOrFail($id);
        
        // Revocar todos los tokens Sanctum asociados a este dispositivo
        $device->sessions()->each(function ($session) {
            // Buscar el personal access token por device_id y revocarlo
        });
        
        // Marcar como inactivo
        $device->update(['is_active' => false]);

        // Eliminar tokens de Sanctum para este dispositivo
        $request->user()->tokens()
            ->where('name', 'device_' . $device->id)
            ->each(fn($t) => $t->delete());

        return response()->json(['message' => 'Dispositivo desconectado correctamente']);
    }

    /**
     * Cierra sesión en TODOS los dispositivos del usuario.
     */
    public function destroyAll(Request $request)
    {
        $request->user()->tokens()->delete();
        $request->user()->devices()->update(['is_active' => false]);

        return response()->json(['message' => 'Todos los dispositivos han sido desconectados']);
    }
}
