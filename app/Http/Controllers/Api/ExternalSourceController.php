<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExternalSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ExternalSourceController extends Controller
{
    // GET /external-sources  — Listar fuentes del usuario
    public function index(Request $request)
    {
        $sources = $request->user()->externalSources()->get();
        return response()->json($sources);
    }

    // POST /external-sources  — Agregar nueva fuente
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'type'     => 'required|in:m3u,mxl,stremio,direct_url',
            'url'      => 'required|string',
            'username' => 'nullable|string|max:100',
            'password' => 'nullable|string|max:100',
        ]);

        $source = $request->user()->externalSources()->create($data);

        // Si es M3U o MXL, sincronizar de inmediato para contar canales
        if (in_array($source->type, ['m3u', 'mxl'])) {
            $source = $this->syncM3u($source);
        }

        return response()->json($source, 201);
    }

    // PUT /external-sources/{id}  — Editar fuente
    public function update(Request $request, $id)
    {
        $source = $request->user()->externalSources()->findOrFail($id);

        $data = $request->validate([
            'name'      => 'sometimes|string|max:100',
            'url'       => 'sometimes|string',
            'username'  => 'nullable|string|max:100',
            'password'  => 'nullable|string|max:100',
            'is_active' => 'sometimes|boolean',
        ]);

        $source->update($data);
        return response()->json($source);
    }

    // DELETE /external-sources/{id}  — Eliminar fuente
    public function destroy(Request $request, $id)
    {
        $source = $request->user()->externalSources()->findOrFail($id);
        $source->delete();
        return response()->json(['message' => 'Fuente eliminada']);
    }

    // POST /external-sources/{id}/sync  — Re-sincronizar
    public function sync(Request $request, $id)
    {
        $source = $request->user()->externalSources()->findOrFail($id);

        if (in_array($source->type, ['m3u', 'mxl'])) {
            $source = $this->syncM3u($source);
        }

        return response()->json([
            'message'        => 'Sincronización completada',
            'channels_count' => $source->channels_count,
            'last_synced_at' => $source->last_synced_at,
        ]);
    }

    // POST /external-sources/validate  — Validar URL antes de guardar
    public function validate_url(Request $request)
    {
        $request->validate(['url' => 'required|string', 'type' => 'required|string']);

        $url  = $request->url;
        $type = $request->type;

        try {
            if ($type === 'stremio') {
                // Stremio: verificar manifest
                $manifestUrl = rtrim($url, '/') . '/manifest.json';
                $response = Http::timeout(8)->get($manifestUrl);
                if ($response->ok()) {
                    $manifest = $response->json();
                    return response()->json([
                        'valid'       => true,
                        'addon_name'  => $manifest['name'] ?? 'Addon Stremio',
                        'description' => $manifest['description'] ?? '',
                    ]);
                }
                return response()->json(['valid' => false, 'message' => 'No se pudo conectar al addon Stremio']);
            }

            if (in_array($type, ['m3u', 'mxl', 'direct_url'])) {
                $response = Http::timeout(10)->head($url);
                return response()->json([
                    'valid'   => $response->status() < 400,
                    'message' => $response->status() < 400 ? 'URL accesible' : 'URL no accesible',
                ]);
            }

            return response()->json(['valid' => false, 'message' => 'Tipo no soportado']);
        } catch (\Exception $e) {
            return response()->json(['valid' => false, 'message' => 'No se pudo conectar: ' . $e->getMessage()]);
        }
    }

    // ── Privado: sincronizar lista M3U / MXL ──────────────────────────────────
    private function syncM3u(ExternalSource $source): ExternalSource
    {
        try {
            $response = Http::timeout(30)->get($source->url);
            if ($response->ok()) {
                $content = $response->body();
                // Contar entradas #EXTINF
                $count = substr_count($content, '#EXTINF');
                $source->update([
                    'channels_count' => $count,
                    'last_synced_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            // Fallo silencioso, no bloquear al usuario
        }

        return $source->fresh();
    }
}
