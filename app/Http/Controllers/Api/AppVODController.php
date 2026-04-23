<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StremioAddon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class AppVODController extends Controller
{
    /**
     * Obtiene el catálogo consolidado de todos los addons de Stremio.
     */
    public function getCatalogs()
    {
        // Desactivado temporalmente para evitar errores 500 si la tabla no existe
        return response()->json([]);
    }

    /**
     * Proxy para obtener items de un catálogo específico (CORS bypass y formato)
     */
    public function getCatalogItems(Request $request)
    {
        $baseUrl = $request->query('base_url');
        $type = $request->query('type');
        $id = $request->query('id');

        if (!$baseUrl || !$type || !$id) {
            return response()->json(['error' => 'Parámetros insuficientes'], 400);
        }

        try {
            $url = "{$baseUrl}catalog/{$type}/{$id}.json";
            $response = Http::timeout(10)->get($url);
            
            if ($response->successful()) {
                return response()->json($response->json());
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['metas' => []]);
    }
}
