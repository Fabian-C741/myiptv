<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StremioAddon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AppVODController extends Controller
{
    protected const CACHE_TTL = 3600;

    public function getCatalogs()
    {
        return Cache::remember('stremio_catalogs', self::CACHE_TTL, function () {
            $addons = StremioAddon::where('is_active', true)->get();
            $consolidated = [];

            foreach ($addons as $addon) {
                try {
                    $response = Http::timeout(5)->get($addon->manifest_url);
                    if ($response->successful()) {
                        $manifest = $response->json();

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

            return $consolidated;
        });
    }

    public function getCatalogItems(Request $request)
    {
        $request->validate([
            'base_url' => 'required|url',
            'type' => 'required|string',
            'id' => 'required|string',
        ]);

        $baseUrl = rtrim($request->base_url, '/');
        $type = $request->type;
        $id = $request->id;

        try {
            $response = Http::timeout(10)->get("{$baseUrl}/catalog/{$type}/{$id}.json");

            return $response->successful()
                ? response()->json($response->json())
                : response()->json(['metas' => []]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getMeta(Request $request, $type, $id)
    {
        $request->validate([
            'base_url' => 'required|url',
        ]);

        $baseUrl = rtrim($request->base_url, '/');

        try {
            $response = Http::timeout(10)->get("{$baseUrl}/meta/{$type}/{$id}.json");

            return $response->successful()
                ? response()->json($response->json())
                : response()->json(['meta' => null]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getStream(Request $request, $type, $id)
    {
        $request->validate([
            'base_url' => 'required|url',
        ]);

        $baseUrl = rtrim($request->base_url, '/');

        try {
            $response = Http::timeout(15)->get("{$baseUrl}/stream/{$type}/{$id}.json");

            return $response->successful()
                ? response()->json($response->json())
                : response()->json(['streams' => []]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
