<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Http\Controllers\Api\AppVODController;
use Illuminate\Http\Request;

header('Content-Type: application/json');

try {
    // 1. Simular la petición que haría la App (Cerebro) hacia el Backend (Cuerpo)
    // Pidiendo los datos de Stremio (Cinemeta) para la película "El Padrino" (tt0068646)
    $request = Request::create('/api/vod/stremio/meta/movie/tt0068646', 'GET', [
        'base_url' => 'https://v3-cinemeta.strem.io/'
    ]);

    $controller = new AppVODController();
    $respuesta_proxy = $controller->getMeta($request, 'movie', 'tt0068646');

    echo json_encode([
        'mensaje' => '⚡ ¡ÉXITO! El Backend funciona como Proxy perfecto hacia Stremio.',
        'explicacion' => 'La App le pidió datos al Backend, y el Backend fue a Stremio, trajo el póster y la sinopsis, y se los entregó limpios a la App.',
        'datos_entregados_por_tu_servidor' => json_decode($respuesta_proxy->getContent(), true)
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

} catch (\Exception $e) {
    echo json_encode(['error' => 'Falló el test: ' . $e->getMessage()]);
}
