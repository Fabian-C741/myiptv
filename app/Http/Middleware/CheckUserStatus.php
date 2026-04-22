<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserStatus
{
    /**
     * Bloquea el acceso si el usuario está suspendido,
     * incluso si tiene un token válido.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        
        if ($user && $user->status === 'suspended') {
            // Revocar el token actual para forzar re-login
            $user->currentAccessToken()?->delete();
            return response()->json([
                'message' => 'Tu cuenta ha sido suspendida. Contacta al administrador.',
                'code' => 'ACCOUNT_SUSPENDED'
            ], 403);
        }

        return $next($request);
    }
}
