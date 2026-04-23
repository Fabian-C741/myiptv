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
        // Auto-reparación: Crear tabla si no existe en Hostinger
        if (!\Illuminate\Support\Facades\Schema::hasTable('stremio_addons')) {
            \Illuminate\Support\Facades\Schema::create('stremio_addons', function ($table) {
                $table->id();
                $table->string('name');
                $table->string('manifest_url')->unique();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        return Cache::remember('stremio_catalogs', 3600, function () {
            $addons = StremioAddon::where('is_active', true)->get();
            $consolidated = [];

            foreach ($addons as $addon) {
                try {
                    // Consultamos el manifest para ver qué catálogos tiene
                    $response = Http::timeout(5)->get($addon->manifest_url);
                    if ($response->successful()) {
                        $manifest = $response->json();
                        
                        // Por cada catálogo (movie, series, etc)
                        foreach ($manifest['catalogs'] ?? [] as $catalog) {
                            $consolidated[] = [
                                'addon_name' => $addon->name,
                                'addon_url' => str_replace('manifest.json', '', $addon->manifest_url),
                                'type' => $catalog['type'],
                                'id' => $catalog['id'],
                                'name' => $catalog['name'] ?? $addon->name,
                            ];
                        }
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            return response()->json($consolidated);
        });
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
