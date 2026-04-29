<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ConfigController extends Controller
{
    /**
     * Vista de Ajustes de Marca para el Panel Web.
     */
    public function index()
    {
        $settings = [
            'app_name'        => Setting::get('app_name', 'Electrofabiptv'),
            'app_version'     => Setting::get('app_version', '1.0.0'),
            'app_apk_url'     => Setting::get('app_apk_url'),
            'primary_color'   => Setting::get('primary_color', '#ff3333'),
            'secondary_color' => Setting::get('secondary_color', '#00aaff'),
            'app_logo'        => Setting::get('app_logo'),
            'whatsapp_contact'=> Setting::get('whatsapp_contact', '+5491100000000'),
        ];

        return view('admin.config.index', compact('settings'));
    }

    /**
     * Retorna la configuración para la APP (API).
     */
    public function show()
    {
        $whatsappRaw   = Setting::get('whatsapp_contact', '+5491100000000');
        $whatsappClean = preg_replace('/[^0-9]/', '', $whatsappRaw);
        $whatsappUrl   = 'https://wa.me/' . $whatsappClean;

        $logoPath = Setting::get('app_logo');
        $logoUrl  = $logoPath ? url(Storage::url($logoPath)) : url('/logo.png');

        return response()->json([
            'app_name'            => Setting::get('app_name'),
            'app_logo'            => $logoUrl,
            'current_version'     => Setting::get('app_version'),
            'apk_url'             => Setting::get('app_apk_url'),
            'whatsapp_contact'    => $whatsappRaw,
            'whatsapp_url'        => $whatsappUrl,
            'default_max_devices' => config('ott.default_max_devices'),
        ]);
    }

    /**
     * Actualiza la configuración global.
     */
    public function update(Request $request)
    {
        $request->validate([
            'app_name'        => 'sometimes|string|max:50',
            'app_version'     => 'sometimes|string',
            'app_apk_url'     => 'sometimes|url|nullable',
            'primary_color'   => 'sometimes|string',
            'secondary_color' => 'sometimes|string',
            'whatsapp_contact'=> 'sometimes|string',
            'logo_file'       => 'sometimes|image|mimes:png,jpg,jpeg|max:2048',
            'apk_file'        => 'nullable|file|max:102400',
        ]);

        // Guardar ajustes generales (todo excepto archivos)
        foreach ($request->except(['_token', 'logo_file', 'apk_file']) as $key => $value) {
            Setting::set($key, $value);
        }

        // Logo
        if ($request->hasFile('logo_file')) {
            $path = $request->file('logo_file')->store('public/brand');
            Setting::set('app_logo', $path);
        }

        // ─── APK: guardado + detección automática de versión ──────────────────
        if ($request->hasFile('apk_file')) {
            try {
                $apkName = 'Electrofabiptv.apk';
                $apkFile = $request->file('apk_file');

                // 1. Guardar el archivo usando la ruta real del servidor
                $docRoot = $_SERVER['DOCUMENT_ROOT'];
                $destinationPath = $docRoot . '/storage/updates';
                
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                $apkFile->move($destinationPath, $apkName);

                // 2. URL pública automática corregida
                $apkUrl = url('storage/updates/' . $apkName);
                Setting::set('app_apk_url', $apkUrl);

            } catch (\Exception $e) {
                return back()->withErrors(['apk_file' => 'Error al guardar el APK: ' . $e->getMessage()]);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message'         => 'Configuración actualizada',
                'detected_version'=> Setting::get('app_version'),
            ]);
        }

        return redirect()->back()->with('success', 'Configuración actualizada. Versión detectada: ' . Setting::get('app_version'));
    }

    /**
     * Lee la versionName directamente del binario AndroidManifest.xml dentro del APK.
     * El APK es un ZIP; el AndroidManifest.xml está en formato binario AXML.
     * El string pool del AXML contiene las cadenas legibles, incluida la versión.
     */
    private function readApkVersion(string $apkPath): ?string
    {
        try {
            $zip = new ZipArchive();
            if ($zip->open($apkPath) !== true) return null;

            $manifest = $zip->getFromName('AndroidManifest.xml');
            $zip->close();

            if (!$manifest || strlen($manifest) < 36) return null;

            // ── Parseo del String Pool del AXML binario ──────────────────────
            // Offset 0-7   : Cabecera AXML (magic 0x00080003 + tamaño total)
            // Offset 8     : Comienza el chunk del String Pool
            $spOffset = 8;

            // Tipo del chunk debe ser 0x0001 (String Pool)
            $chunkType = unpack('v', substr($manifest, $spOffset, 2))[1];
            if ($chunkType !== 0x0001) return null;

            $headerSize  = unpack('v', substr($manifest, $spOffset + 2, 2))[1];
            $stringCount = unpack('V', substr($manifest, $spOffset + 8,  4))[1];
            $styleCount  = unpack('V', substr($manifest, $spOffset + 12, 4))[1];
            $flags       = unpack('V', substr($manifest, $spOffset + 16, 4))[1];
            $stringsStart= unpack('V', substr($manifest, $spOffset + 20, 4))[1];

            $isUtf8      = ($flags & 0x100) !== 0;
            $offsetsBase = $spOffset + $headerSize;
            $strBase     = $spOffset + $stringsStart;

            $strings = [];
            for ($i = 0; $i < $stringCount; $i++) {
                $strOff = unpack('V', substr($manifest, $offsetsBase + $i * 4, 4))[1];
                $pos    = $strBase + $strOff;

                if ($isUtf8) {
                    // Longitud en bytes (segunda longitud, segunda posición)
                    $len = ord($manifest[$pos + 1]);
                    $str = substr($manifest, $pos + 2, $len);
                } else {
                    // UTF-16LE: longitud en caracteres de 2 bytes
                    $len = unpack('v', substr($manifest, $pos, 2))[1];
                    $str = mb_convert_encoding(
                        substr($manifest, $pos + 2, $len * 2),
                        'UTF-8',
                        'UTF-16LE'
                    );
                }
                $strings[] = $str;
            }

            // Buscar la versión entre las cadenas del pool (ej: "1.0.4" o "1.0.4+25")
            foreach ($strings as $s) {
                $s = trim($s);
                if (preg_match('/^\d+\.\d+(\.\d+)*(\+\d+)?$/', $s)) {
                    return $s;
                }
            }

            return null;

        } catch (\Throwable $e) {
            return null;
        }
    }
}
