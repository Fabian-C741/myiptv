<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class ConfigController extends Controller
{
    /**
     * Vista de Ajustes de Marca para el Panel Web.
     */
    public function index()
    {
        $settings = [
            'app_name' => Setting::get('app_name', 'Electrofabiptv'),
            'app_version' => Setting::get('app_version', '1.0.0'),
            'app_apk_url' => Setting::get('app_apk_url'),
            'primary_color' => Setting::get('primary_color', '#ff3333'),
            'secondary_color' => Setting::get('secondary_color', '#00aaff'),
            'app_logo' => Setting::get('app_logo'),
        ];

        return view('admin.config.index', compact('settings'));
    }

    /**
     * Retorna la configuración para la APP (API).
     */
    public function show()
    {
        return response()->json([
            'app_name' => Setting::get('app_name'),
            'app_logo' => Setting::get('app_logo') ? url(Storage::url(Setting::get('app_logo'))) : null,
            'current_version' => Setting::get('app_version'),
            'apk_url' => Setting::get('app_apk_url'),
            'default_max_devices' => config('ott.default_max_devices'),
        ]);
    }

    /**
     * Actualiza la configuración global.
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'app_name' => 'sometimes|string|max:50',
            'app_version' => 'sometimes|string',
            'app_apk_url' => 'sometimes|url|nullable',
            'primary_color' => 'sometimes|string',
            'secondary_color' => 'sometimes|string',
            'logo_file' => 'sometimes|image|mimes:png,jpg,jpeg|max:2048'
        ]);

        foreach ($request->except(['_token', 'logo_file']) as $key => $value) {
            Setting::set($key, $value);
        }

        $request->validate([
            'apk_file' => 'nullable|file|max:102400', // Máximo 100MB
        ]);

        if ($request->hasFile('logo_file')) {
            $path = $request->file('logo_file')->store('public/brand');
            Setting::set('app_logo', $path);
        }

        // Lógica Automática para el APK
        if ($request->hasFile('apk_file')) {
            try {
                // 1. Guardar el archivo APK
                $apkName = 'Electrofabiptv.apk';
                $request->file('apk_file')->storeAs('public/updates', $apkName);
                
                // 2. Generar la URL automática
                $apkUrl = url(Storage::url('updates/' . $apkName));
                Setting::set('app_apk_url', $apkUrl);

                // 3. Auto-incrementar la versión
                $currentVersion = Setting::get('app_version', '1.0.0');
                $parts = explode('.', $currentVersion);
                if (count($parts) == 3) {
                    $parts[2] = (int)$parts[2] + 1; // Incrementamos el último número
                    $newVersion = implode('.', $parts);
                    Setting::set('app_version', $newVersion);
                }
            } catch (\Exception $e) {
                return back()->withErrors(['apk_file' => 'Error al guardar el APK: ' . $e->getMessage()]);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Configuración actualizada']);
        }

        return redirect()->back()->with('success', 'Configuración de marca actualizada exitosamente.');
    }
}
