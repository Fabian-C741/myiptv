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

    // Vamos a intentar guardar el primer canal encontrado para forzar a la DB a darnos el error
    $db_error = 'Ninguno. Se guardó correctamente.';
    
    // Buscar la primera URL y el primer nombre en el preview
    preg_match('/group-title="([^"]+)"/i', $body, $groupMatch);
    $safeGroupName = substr($groupMatch[1] ?? 'General', 0, 100);
    
    // Buscar primera URL que empiece con http
    preg_match('/(https?:\/\/[^\r\n]+)/', $body, $urlMatch);
    $safeUrl = substr($urlMatch[1] ?? 'http://test.com', 0, 250);
    
    try {
        $group = \App\Models\ChannelGroup::updateOrCreate(
            ['playlist_id' => $playlist->id, 'name' => $safeGroupName, 'type' => 'live'],
            ['is_adult' => false]
        );

        \App\Models\Channel::updateOrCreate(
            ['playlist_id' => $playlist->id, 'stream_url' => $safeUrl],
            [
                'channel_group_id' => $group->id,
                'type' => 'live',
                'name' => 'Prueba Diagnostico',
                'logo' => null,
                'is_adult' => false
            ]
        );
    } catch (\Exception $e) {
        $db_error = $e->getMessage();
    }

    $stats = [
        'lista_nombre' => $playlist->name,
        'http_status' => $response->status(),
        'etiquetas_extinf_encontradas' => $extinf_count,
        'ERROR_DE_BASE_DE_DATOS_EXACTO' => $db_error,
        'vista_previa' => $preview
    ];

    echo json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

} catch (\Exception $e) {
    echo json_encode(['error_fatal' => $e->getMessage()]);
}
