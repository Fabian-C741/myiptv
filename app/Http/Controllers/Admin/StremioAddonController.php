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
            // Intentamos obtener el nombre y datos desde el manifest
            $response = Http::get($request->manifest_url);
            if ($response->successful()) {
                $manifest = $response->json();
                
                StremioAddon::create([
                    'name' => $manifest['name'] ?? 'Addon sin nombre',
                    'manifest_url' => $request->manifest_url,
                    'icon' => $manifest['logo'] ?? null,
                    'catalog_types' => $manifest['types'] ?? []
                ]);

                return back()->with('success', 'Addon de Stremio añadido correctamente.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'No se pudo conectar con el manifest: ' . $e->getMessage());
        }

        return back()->with('error', 'El URL del manifest no es válido.');
    }

    public function destroy(StremioAddon $stremioAddon)
    {
        $stremioAddon->delete();
        return back()->with('success', 'Addon eliminado.');
    }
}
