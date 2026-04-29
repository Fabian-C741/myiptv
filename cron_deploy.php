<?php
/**
 * Script de Auto-Despliegue para Electrofabiptv
 * Se sincroniza con GitHub y limpia cachés de Laravel.
 */

$repo_path = "/home/u496356948/public_html/myiptv";
$log_file  = "/home/u496356948/public_html/deploy_cron.log";

function log_message($msg) {
    global $log_file;
    $timestamp = date("Y-m-d H:i:s");
    file_put_contents($log_file, "[$timestamp] $msg\n", FILE_APPEND);
}

log_message("--- Inicio de despliegue automático ---");

// 1. Pull desde GitHub
$output = [];
$status = 0;
exec("cd $repo_path && git pull origin main 2>&1", $output, $status);
log_message("GIT PULL: " . implode("\n", $output));

if ($status === 0) {
    // 2. Limpiar Cachés de Laravel
    exec("php $repo_path/artisan config:clear 2>&1", $output);
    exec("php $repo_path/artisan cache:clear 2>&1", $output);
    exec("php $repo_path/artisan view:clear 2>&1", $output);
    log_message("CACHÉ: Limpieza completada.");

    // 3. Re-vincular storage si es necesario
    if (!file_exists($repo_path . "/public/storage")) {
        exec("php $repo_path/artisan storage:link 2>&1");
        log_message("STORAGE: Link creado.");
    }
} else {
    log_message("ERROR: No se pudo sincronizar con Git.");
}

log_message("--- Fin del despliegue ---\n");
echo "Despliegue finalizado. Revisa deploy_cron.log para más detalles.";
