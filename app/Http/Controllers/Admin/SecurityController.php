<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SecurityAlert;
use App\Models\Device;
use Illuminate\Support\Facades\DB;

class SecurityController extends Controller
{
    /**
     * Lista alertas de seguridad (no resueltas primero).
     */
    public function alerts(Request $request)
    {
        $alerts = SecurityAlert::with('user:id,name,email')
            ->orderBy('resolved', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        return response()->json($alerts);
    }

    /**
     * Marcar alerta como resuelta.
     */
    public function resolve($alertId)
    {
        $alert = SecurityAlert::findOrFail($alertId);
        $alert->update(['resolved' => true]);

        return response()->json(['message' => 'Alerta marcada como resuelta']);
    }

    /**
     * IPs sospechosas: dispositivos con más de 1 usuario distinto en la misma IP.
     */
    public function suspiciousIps()
    {
        $suspicious = Device::select('ip_address', DB::raw('COUNT(DISTINCT user_id) as user_count'))
            ->groupBy('ip_address')
            ->having('user_count', '>', 1)
            ->orderBy('user_count', 'desc')
            ->get();

        return response()->json($suspicious);
    }

    /**
     * Usuarios conectados desde múltiples países simultáneamente.
     */
    public function multiCountryUsers()
    {
        $users = Device::select('user_id', DB::raw('COUNT(DISTINCT country) as country_count'))
            ->where('is_active', true)
            ->whereNotNull('country')
            ->groupBy('user_id')
            ->having('country_count', '>', 1)
            ->with('user:id,name,email')
            ->get();

        return response()->json($users);
    }
    /**
     * Vista Web: Panel de Seguridad.
     */
    public function indexWeb(Request $request)
    {
        // Alertas recientes
        $alerts = SecurityAlert::with('user')
            ->orderBy('resolved', 'asc')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // IPs con múltiples usuarios (con nombres de usuarios)
        $suspiciousIps = Device::select('ip_address', DB::raw('GROUP_CONCAT(DISTINCT user_id) as user_ids'), DB::raw('COUNT(DISTINCT user_id) as user_count'))
            ->groupBy('ip_address')
            ->having('user_count', '>', 1)
            ->orderBy('user_count', 'desc')
            ->take(10)
            ->get();
            
        // Mapear IDs a nombres para mostrar en la vista
        foreach($suspiciousIps as $ip) {
            $ids = explode(',', $ip->user_ids);
            $ip->users = \App\Models\User::whereIn('id', $ids)->get(['id', 'name', 'status']);
        }

        // Usuarios en múltiples países
        $multiCountry = Device::select('user_id', DB::raw('COUNT(DISTINCT country) as country_count'))
            ->where('is_active', true)
            ->whereNotNull('country')
            ->groupBy('user_id')
            ->having('country_count', '>', 1)
            ->with('user:id,name,email')
            ->get();

        return view('admin.security.index', compact('alerts', 'suspiciousIps', 'multiCountry'));
    }
}
