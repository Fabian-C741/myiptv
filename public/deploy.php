<?php
/**
 * GitHub Webhook Auto-Deploy
 * Escucha los push de GitHub y actualiza el servidor automáticamente.
 * Coloca este archivo en: public_html/deploy.php
 */

// ── Configuración ─────────────────────────────────────────────────────────────
define('SECRET',       'ElectroFabi_Deploy_2026');   // Cambiar por tu secret
define('REPO_PATH',    dirname(__DIR__));              // Raíz del proyecto
define('BRANCH',       'master');
define('LOG_FILE',     __DIR__ . '/deploy.log');

// ── Seguridad: solo acepta peticiones de GitHub ───────────────────────────────
$headers = getallheaders();
$signature = $headers['X-Hub-Signature-256'] ?? '';
$payload   = file_get_contents('php://input');

if (empty($signature)) {
    http_response_code(403);
    die(json_encode(['error' => 'Sin firma']));
}

$expected = 'sha256=' . hash_hmac('sha256', $payload, SECRET);
if (!hash_equals($expected, $signature)) {
    http_response_code(403);
    die(json_encode(['error' => 'Firma inválida']));
}

// ── Solo reacciona a push en el branch correcto ───────────────────────────────
$data = json_decode($payload, true);
$ref  = $data['ref'] ?? '';

if ($ref !== 'refs/heads/' . BRANCH) {
    http_response_code(200);
    die(json_encode(['info' => "Ignorado: branch $ref"]));
}

// ── Ejecutar git pull ─────────────────────────────────────────────────────────
$commands = [
    "cd " . REPO_PATH . " && git fetch origin " . BRANCH,
    "cd " . REPO_PATH . " && git reset --hard origin/" . BRANCH,
    "cd " . REPO_PATH . " && php artisan config:clear",
    "cd " . REPO_PATH . " && php artisan cache:clear",
    "cd " . REPO_PATH . " && php artisan route:clear",
    "cd " . REPO_PATH . " && php artisan view:clear",
];

$log = "[" . date('Y-m-d H:i:s') . "] Deploy iniciado por push de GitHub\n";
$output = [];

foreach ($commands as $cmd) {
    $result = shell_exec($cmd . ' 2>&1');
    $log   .= "CMD: $cmd\nOUT: $result\n---\n";
    $output[] = $result;
}

// ── Guardar log ───────────────────────────────────────────────────────────────
file_put_contents(LOG_FILE, $log, FILE_APPEND);

http_response_code(200);
echo json_encode([
    'status'  => 'success',
    'message' => 'Deploy completado',
    'output'  => $output,
]);
