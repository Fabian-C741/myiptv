<?php
// Script de Diagnóstico para ELECTROFABI IPTV
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

use App\Models\Channel;
use App\Models\ChannelGroup;
use App\Models\StremioAddon;

header('Content-Type: application/json');

$stats = [
    'estado' => 'OK',
    'canales_totales' => Channel::count(),
    'grupos_totales' => ChannelGroup::count(),
    'addons_stremio' => StremioAddon::count(),
    'ultimos_5_canales' => Channel::latest()->take(5)->get(['name', 'type']),
];

echo json_with_errors($stats);

function json_with_errors($data) {
    return json_encode($data, JSON_PRETTY_PRINT);
}
