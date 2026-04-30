<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StremioAddon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class StremioAddonController extends Controller
{
    public function index()
    {
        $addons = StremioAddon::all();
        return view('admin.stremio.index', compact('addons'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'manifest_url' => 'required|url'
        ]);

        try {
            // Timeout de 10 segundos para evitar que el panel se congele
            $response = Http::timeout(10)->get($request->manifest_url);

            if ($response->successful()) {
                $manifest = $response->json();

                // Verificar que sea un manifest Stremio válido
                if (empty($manifest['id']) || empty($manifest['name'])) {
                    return back()->with('error', 'La URL no es un manifest de Stremio válido (falta id o name).');
                }

                StremioAddon::create([
                    'name'          => $manifest['name'] ?? 'Addon sin nombre',
                    'manifest_url'  => $request->manifest_url,
                    'icon'          => $manifest['logo'] ?? $manifest['background'] ?? null,
                    'catalog_types' => $manifest['types'] ?? [],
                ]);

                return back()->with('success', '✅ Addon "' . $manifest['name'] . '" conectado correctamente.');
            }

            return back()->with('error', 'El servidor del addon respondió con error ' . $response->status() . '. Verificá que la URL sea correcta.');

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return back()->with('error', '⏱️ Timeout: El servidor del addon tardó demasiado en responder. Intentá de nuevo o probá otro addon.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al conectar: ' . $e->getMessage());
        }
    }

    public function destroy(StremioAddon $stremioAddon)
    {
        $stremioAddon->delete();
        return back()->with('success', 'Addon eliminado.');
    }
}
