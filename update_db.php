<?php
/**
 * ACTUALIZADOR DE BASE DE DATOS - ELECTROFABIPTV
 * Ejecuta este archivo una sola vez para activar la tabla de ajustes.
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Detectar base de Laravel (si está en el root o en /public)
$basePath = __DIR__;
if (!file_exists($basePath.'/vendor/autoload.php')) {
    $basePath = dirname(__DIR__); // Probar un nivel arriba
}

if (!file_exists($basePath.'/vendor/autoload.php')) {
    die("ERROR: No se encontró la carpeta 'vendor'. Asegúrate de subir todos los archivos del proyecto.");
}

require $basePath.'/vendor/autoload.php';
$app = require_once $basePath.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

try {
    echo "=== ACTUALIZANDO BASE DE DATOS (Electrofabiptv) ===\n\n";

    // 1. Crear tabla de ajustes si no existe
    if (!Schema::hasTable('settings')) {
        echo "1. Creando tabla 'settings'...";
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->string('type')->default('string');
            $table->timestamps();
        });
        echo " [OK]\n";
    }

    // 2. Insertar valores iniciales de marca
    $defaults = [
        ['key' => 'app_name', 'value' => 'Electrofabiptv', 'type' => 'string'],
        ['key' => 'app_version', 'value' => '1.0.0', 'type' => 'string'],
        ['key' => 'primary_color', 'value' => '#00aaff', 'type' => 'string'],
        ['key' => 'secondary_color', 'value' => '#ff3333', 'type' => 'string'],
        ['key' => 'app_logo', 'value' => null, 'type' => 'image'],
        ['key' => 'apk_url', 'value' => null, 'type' => 'string'],
    ];

    foreach ($defaults as $row) {
        if (!DB::table('settings')->where('key', $row['key'])->exists()) {
            DB::table('settings')->insert(array_merge($row, ['created_at' => now(), 'updated_at' => now()]));
        }
    }
    echo "2. Valores de marca inicializados... [OK]\n";

    echo "\n¡LISTO! Ya puedes borrar este archivo y entrar a Ajustes en tu Panel Web.";

} catch (\Exception $e) {
    echo "ERROR CRÍTICO: " . $e->getMessage();
}
