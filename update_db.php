<?php
// Script para forzar la configuración de marca en la DB
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

use App\Models\Setting;
use Illuminate\Support\Facades\DB;

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle($request = Illuminate\Http\Request::capture());

try {
    // Forzamos los valores base
    Setting::set('app_name', 'ELECTROFABI IPTV');
    Setting::set('app_version', '2.0.0'); // Ponemos una versión alta para forzar el aviso
    Setting::set('app_apk_url', 'https://streaming-iptv.kcrsf.com/storage/updates/Electrofabiptv.apk');
    
    echo "<h1>✅ Base de datos actualizada</h1>";
    echo "<p>Nombre: ELECTROFABI IPTV</p>";
    echo "<p>Versión forzada: 2.0.0</p>";
    echo "<p>URL APK: https://streaming-iptv.kcrsf.com/storage/updates/Electrofabiptv.apk</p>";
    echo "<hr>";
    echo "<p>Ahora abre la App en tu móvil. Si tiene internet, DEBERÍA salir el cartel de actualización inmediatamente.</p>";

} catch (\Exception $e) {
    echo "<h1>❌ Error</h1>";
    echo $e->getMessage();
}
