<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeviceSession;

class SessionController extends Controller
{
    /**
     * Muestra las sesiones activas del usuario actual.
     */
    public function index(Request $request)
    {
        $sessions = DeviceSession::where('user_id', $request->user()->id)
            ->with(['device:id,device_name,device_type,ip_address,country,city'])
            ->orderBy('created_at', 'desc')
            ->get(['id', 'user_id', 'profile_id', 'device_id', 'expires_at', 'created_at']);

        return response()->json($sessions);
    }

    /**
     * Cierra TODAS las sesiones del usuario (útil si detecta acceso no autorizado).
     */
    public function destroyAll(Request $request)
    {
        $request->user()->tokens()->delete();
        DeviceSession::where('user_id', $request->user()->id)->delete();

        return response()->json(['message' => 'Todas las sesiones han sido cerradas']);
    }
}
