
# ============================================================
# Script Profesional de Construccion Inteligente (Smart Build)
# ============================================================
# Optimizacion: Evita limpiezas redundantes y usa cache persistente en D:
param(
    [switch]$Clean,      # Si se pasa -Clean, se hara una limpieza total
    [switch]$Verbose     # Si se pasa -Verbose, muestra mas detalles
)

# 1. Configuracion de Entorno (Cimientos)
$env:GRADLE_USER_HOME = "D:\Apk-tv\.gradle_cache"
$env:PUB_CACHE = $null # Usamos el cache por defecto para evitar bloqueos
[System.Environment]::SetEnvironmentVariable('GRADLE_USER_HOME', $env:GRADLE_USER_HOME, 'Process')

$junctionPath = "C:\Apk-tv-Direct"
$projectRoot = "D:\Apk-tv\Apk"

Write-Host ">>> Iniciando Proceso de Construccion Inteligente" -ForegroundColor Cyan

# 2. Gestion de Limpieza (Solo si es necesario)
if ($Clean) {
    Write-Host "--- Modo LIMPIEZA TOTAL activado ---" -ForegroundColor Yellow
    Remove-Item -Recurse -Force "$projectRoot\build" -ErrorAction SilentlyContinue
    Remove-Item -Recurse -Force "$projectRoot\.dart_tool" -ErrorAction SilentlyContinue
    Remove-Item -Recurse -Force "$projectRoot\.gradle" -ErrorAction SilentlyContinue
    # No borramos .gradle_cache por defecto a menos que sea un desastre total
}

# 3. Gestion del Puente (Junction)
if (!(Test-Path $junctionPath)) {
    Write-Host "  > Creando puente en C: para compatibilidad..." -ForegroundColor Gray
    cmd /c "mklink /J C:\Apk-tv-Direct D:\Apk-tv"
}

# 4. Construccion Incremental
Set-Location "$junctionPath\Apk"

# Solo ejecutamos pub get si faltan archivos vitales o se pide limpieza
if ($Clean -or !(Test-Path ".dart_tool")) {
    Write-Host "  > Resolviendo dependencias (pub get)..." -ForegroundColor Gray
    flutter pub get
}

Write-Host "  > Generando icono de la app..." -ForegroundColor Gray
flutter pub run flutter_launcher_icons

Write-Host "  > Compilando APK Release (Construccion Incremental)..." -ForegroundColor Gray
# Usamos flags para estabilidad y evitar errores de fuentes
flutter build apk --release --no-tree-shake-icons

# 5. Resultado Final
if ($LASTEXITCODE -eq 0) {
    $realApkPath = "D:\Apk-tv\Apk\build\app\outputs\flutter-apk\app-release.apk"
    Write-Host "`n==========================================" -ForegroundColor Green
    Write-Host "   APK GENERADO EXITOSAMENTE!" -ForegroundColor Green
    Write-Host "==========================================" -ForegroundColor Green
    Write-Host "Ubicacion: $realApkPath" -ForegroundColor Yellow
} else {
    Write-Host "`n!!! Error en la compilacion. Intenta ejecutar con el parametro -Clean si el error persiste." -ForegroundColor Red
}
