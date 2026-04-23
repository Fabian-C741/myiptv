<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\Channel;

header('Content-Type: application/json');

try {
    $canales = Channel::latest()->take(5)->get(['id', 'name', 'stream_url']);
    
    $resultado = [];
    foreach ($canales as $canal) {
        $resultado[] = [
            'nombre' => $canal->name,
            'url_en_bd' => $canal->stream_url,
            'longitud_url' => strlen($canal->stream_url),
            'posiblemente_incompleta' => strlen($canal->stream_url) == 250 // El límite que le pusimos al truncar
        ];
    }

    echo json_encode([
        'mensaje' => 'Testeo de integridad de URLs enviadas a la App',
        'canales_analizados' => $resultado
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

} catch (\Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
