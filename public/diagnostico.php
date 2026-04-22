<?php
/**
 * Script de Diagnóstico Seguro para Servidor de Producción.
 * Diseñado para detectar errores de Laravel 500 sin revelar contraseñas del .env.
 */

header('Content-Type: text/plain; charset=utf-8');
echo "=== REPORTE DE DIAGNÓSTICO DEL SERVIDOR ===\n\n";

// 1. Cargar variables del archivo .env de forma segura
$envPath = __DIR__ . '/../.env';
if (!file_exists($envPath)) {
    echo "[ERROR] El archivo .env no se encontró en la raíz del proyecto.\n";
    die();
}

$envVariables = parse_ini_file($envPath);
$dbHost = $envVariables['DB_HOST'] ?? '127.0.0.1';
$dbName = $envVariables['DB_DATABASE'] ?? '';
$dbUser = $envVariables['DB_USERNAME'] ?? '';
$dbPass = $envVariables['DB_PASSWORD'] ?? '';

echo "1. DATOS DE CONEXIÓN A BASE DE DATOS (Ocultos por seguridad):\n";
echo "   Host: " . $dbHost . "\n";
echo "   Base de datos: " . $dbName . "\n";
echo "   Usuario: " . $dbUser . "\n";
echo "   Contraseña configurada: " . (!empty($dbPass) ? 'Sí (Oculta)' : '¡ADVERTENCIA! Vacía') . "\n\n";

// 2. Probar Conexión Activa por PDO
echo "2. PRUEBA DE CONEXIÓN A MYSQL:\n";
try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "   [ÉXITO] La conexión a la base de datos funciona perfectamente.\n\n";
    
    // Verificar si las tablas (migraciones) existen realmente
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "   [ÉXITO] La tabla 'users' existe (Las migraciones fueron ejecutadas).\n\n";
    } else {
        echo "   [ERROR FATAL] La base de datos existe y conecta, pero ESTÁ VACÍA (no tiene la tabla users). Laravel fallará.\n\n";
    }
} catch (PDOException $e) {
    echo "   [ERROR FATAL] Falló la conexión a MySQL: " . $e->getMessage() . "\n\n";
}

// 3. Revisar permisos de carpetas requeridas por Laravel
echo "3. PERMISOS DE CARPETAS:\n";
$storagePath = __DIR__ . '/../storage';
$cachePath = __DIR__ . '/../bootstrap/cache';

echo "   Storage Writeable: " . (is_writable($storagePath) ? '[OK]' : '[ERROR] No tiene permisos de escritura') . "\n";
echo "   Cache Writeable: " . (is_writable($cachePath) ? '[OK]' : '[ERROR] No tiene permisos de escritura') . "\n\n";

// 4. Últimos Errores de Laravel en Vivo (Filtrados)
echo "4. ÚLTIMOS ERRORES REGISTRADOS EN LOG:\n";
$logFile = $storagePath . '/logs/laravel.log';

if (file_exists($logFile)) {
    $lines = file($logFile);
    // Extraer los últimos errores verdaderos de Laravel buscando la palabra "ERROR"
    $recentErrors = array_slice(preg_grep('/local\.ERROR/i', $lines), -5);
    
    if (empty($recentErrors)) {
        echo "   No se registraron errores críticos recientes en el log de Laravel.\n";
    } else {
        foreach ($recentErrors as $error) {
            // Recortar la cadena para limpiar datos sensibles si los hubiese
            $cleanError = substr($error, 0, 800) . "..."; 
            echo "   -> " . trim($cleanError) . "\n";
        }
    }
} else {
    echo "   [INFO] El archivo laravel.log no existe aún (No hay errores guardados).\n";
}

echo "\n--- FIN DEL DIAGNÓSTICO ---";
