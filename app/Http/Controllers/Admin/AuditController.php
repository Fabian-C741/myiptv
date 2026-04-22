<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuditLog;

class AuditController extends Controller
{
    /**
     * Lista logs de auditoría con filtros opcionales.
     */
    public function index(Request $request)
    {
        $query = AuditLog::orderBy('created_at', 'desc');

        // Filtrar por tipo de acción
        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        // Filtrar por IP
        if ($request->has('ip')) {
            $query->where('ip_address', $request->ip);
        }

        // Filtrar por fecha
        if ($request->has('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->has('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        return response()->json($query->paginate(50));
    }

    /**
     * Resumen de acciones agrupadas por tipo (para dashboard).
     */
    public function summary()
    {
        $summary = AuditLog::selectRaw('action, COUNT(*) as total')
            ->groupBy('action')
            ->orderBy('total', 'desc')
            ->get();

        return response()->json($summary);
    }
}
