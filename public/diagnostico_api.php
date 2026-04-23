<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\Playlist;
use Illuminate\Support\Facades\Http;

header('Content-Type: application/json');

$playlist = Playlist::where('type', 'm3u')->first();

if (!$playlist) {
    echo json_encode(['error' => 'No se encontró ninguna lista M3U en la base de datos para probar.']);
    exit;
}

try {
    $response = Http::withHeaders([
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
    ])->timeout(30)->get($playlist->url);

    $body = $response->body();
    $preview = substr($body, 0, 800); // Primeros 800 caracteres
    
    // Contar ocurrencias manuales
    $extinf_count = substr_count($body, '#EXTINF:');

    $stats = [
        'lista_nombre' => $playlist->name,
        'lista_url' => $playlist->url,
        'http_status' => $response->status(),
        'body_length' => strlen($body),
        'etiquetas_extinf_encontradas' => $extinf_count,
        'vista_previa_contenido' => $preview
    ];

    echo json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

} catch (\Exception $e) {
    echo json_encode([
        'error_fatal' => $e->getMessage()
    ]);
}
