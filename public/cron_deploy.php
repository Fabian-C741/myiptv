#!/usr/bin/env php
<?php
/**
 * Auto-Deploy por Cron Job
 * Compara el último commit de GitHub con el local.
 * Si hay cambios, hace git pull automáticamente.
 *
 * CONFIGURAR EN CPANEL CRON JOBS:
 * Cada 5 minutos: * /5 * * * * /usr/bin/php /home/TU_USUARIO/cron_deploy.php
 */

// ── Configuración ─────────────────────────────────────────────────────────────
$repoOwner  = 'Fabian-C741';
$repoName   = 'myiptv';
$branch     = 'master';
$projectDir = '/home/u496356948';                // Raíz del proyecto Laravel
$logFile    = '/home/u496356948/public_html/deploy_cron.log';
$maxLogSize = 500 * 1024;               // Rotar log si supera 500KB

// ── Rotar log ─────────────────────────────────────────────────────────────────
if (file_exists($logFile) && filesize($logFile) > $maxLogSize) {
    rename($logFile, $logFile . '.bak');
}

function log_msg(string $msg): void {
    global $logFile;
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] $msg\n", FILE_APPEND);
}

// ── Obtener último commit en GitHub (API pública, sin auth) ───────────────────
$apiUrl  = "https://api.github.com/repos/{$repoOwner}/{$repoName}/commits/{$branch}";
$context = stream_context_create([
    'http' => [
        'timeout'       => 10,
        'user_agent'    => 'ElectroFabi-AutoDeploy/1.0',
    ]
]);

$response = @file_get_contents($apiUrl, false, $context);
if (!$response) {
    log_msg("ERROR: No se pudo conectar con la API de GitHub.");
    exit(1);
}

$data          = json_decode($response, true);
$remoteCommit  = $data['sha'] ?? null;

if (!$remoteCommit) {
    log_msg("ERROR: No se pudo obtener el SHA del commit remoto.");
    exit(1);
}

// ── Obtener commit local ──────────────────────────────────────────────────────
$localCommit = trim(shell_exec("cd {$projectDir} && git rev-parse HEAD 2>&1"));

// ── Comparar ──────────────────────────────────────────────────────────────────
if ($localCommit === $remoteCommit) {
    // Sin cambios, salir silenciosamente
    exit(0);
}

// ── Hay cambios: hacer deploy ─────────────────────────────────────────────────
log_msg("🔄 Nuevo commit detectado: {$remoteCommit} (local: {$localCommit})");
log_msg("🚀 Iniciando auto-deploy...");

$commands = [
    "cd {$projectDir} && git fetch origin {$branch}",
    "cd {$projectDir} && git reset --hard origin/{$branch}",
    "cd {$projectDir} && php artisan config:clear",
    "cd {$projectDir} && php artisan cache:clear",
    "cd {$projectDir} && php artisan route:clear",
    "cd {$projectDir} && php artisan view:clear",
];

$allOk = true;
foreach ($commands as $cmd) {
    $out = shell_exec($cmd . ' 2>&1');
    log_msg("  CMD: {$cmd}");
    log_msg("  OUT: " . trim($out));
    if (str_contains($out ?? '', 'error') || str_contains($out ?? '', 'fatal')) {
        $allOk = false;
    }
}

if ($allOk) {
    log_msg("✅ Deploy completado exitosamente.");
} else {
    log_msg("⚠️  Deploy completado con advertencias. Revisar log.");
}

exit(0);
