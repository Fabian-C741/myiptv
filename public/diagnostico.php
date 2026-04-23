<?php
// Diagnóstico de Versión y API para ELECTROFABI IPTV
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

header('Content-Type: application/json');

echo json_encode([
    'app_name' => Setting::get('app_name'),
    'current_version_in_database' => Setting::get('app_version'),
    'apk_url' => Setting::get('app_apk_url'),
    'storage_link_works' => file_exists(public_path('storage')) ? 'YES' : 'NO',
    'apk_file_exists' => file_exists(storage_path('app/public/updates/Electrofabiptv.apk')) ? 'YES' : 'NO',
    'php_version' => PHP_VERSION,
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
]);
