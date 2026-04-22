<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $admin = $request->user();
        
        if (!$admin) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        if (!empty($roles) && !in_array($admin->role, $roles)) {
            return response()->json(['message' => 'Sin permisos suficientes'], 403);
        }

        return $next($request);
    }
}
