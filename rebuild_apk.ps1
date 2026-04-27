# Automated Build Script for Electrofabiptv
# Goal: Rebuild a solid, clean, and production-ready APK

Write-Host "--- Iniciando reconstrucción sólida de Electrofabiptv ---" -ForegroundColor Cyan

# 1. Limpieza Profunda
Write-Host "[1/4] Realizando limpieza profunda de Flutter y Gradle..." -ForegroundColor Yellow
cd Apk
flutter clean
if (Test-Path "android/.gradle") { Remove-Item -Recurse -Force android/.gradle }
if (Test-Path "android/app/build") { Remove-Item -Recurse -Force android/app/build }

# 2. Obtener Dependencias
Write-Host "[2/4] Sincronizando dependencias..." -ForegroundColor Yellow
flutter pub get

Write-Host "[2.5/4] Generando Iconos de la Aplicación..." -ForegroundColor Yellow
flutter pub run flutter_launcher_icons

# 3. Compilación de Producción
Write-Host "[3/4] Compilando APK de Producción (Release)..." -ForegroundColor Yellow
Write-Host "(Nota: El procesamiento paralelo está desactivado para estabilidad extrema)"
flutter build apk --release --target-platform android-arm,android-arm64,android-x64

# 4. Organización del resultado
Write-Host "[4/4] Desplegando APK en carpeta pública..." -ForegroundColor Yellow
$apkPath = "build\app\outputs\flutter-apk\app-release.apk"

if (Test-Path $apkPath) {
    # Carpeta de respaldo interna
    $backupDir = "..\release_apks"
    if (!(Test-Path $backupDir)) { New-Item -ItemType Directory -Path $backupDir }
    $timestamp = Get-Date -Format "yyyyMMdd_HHmm"
    Copy-Item $apkPath -Destination "$backupDir\Electrofabiptv_v1.0.0_$timestamp.apk"

    # Carpeta de descarga para la App (Laravel Storage)
    $storageUpdatesDir = "..\storage\app\public\updates"
    if (!(Test-Path $storageUpdatesDir)) { New-Item -ItemType Directory -Path $storageUpdatesDir }
    
    $storagePath = "$storageUpdatesDir\Electrofabiptv.apk"
    Copy-Item $apkPath -Destination $storagePath -Force

    # También mantenemos en public/apk por si acaso
    $publicApkDir = "..\public\apk"
    if (!(Test-Path $publicApkDir)) { New-Item -ItemType Directory -Path $publicApkDir }
    Copy-Item $apkPath -Destination "$publicApkDir\Electrofabiptv.apk" -Force
    
    Write-Host "--------------------------------------------------------" -ForegroundColor Green
    Write-Host "¡COMPILACIÓN Y DESPLIEGUE EXITOSO!" -ForegroundColor Green
    Write-Host "APK desplegado en: $storagePath" -ForegroundColor Cyan
    Write-Host "URL de descarga: https://streaming-iptv.kcrsf.com/storage/updates/Electrofabiptv.apk" -ForegroundColor Green
    Write-Host "--------------------------------------------------------" -ForegroundColor Green
    
    # Intentar actualizar la versión en la DB si php está disponible
    Write-Host "Actualizando versión en la base de datos..." -ForegroundColor Yellow
    php ..\artisan tinker --execute="App\Models\Setting::set('app_apk_url', 'https://streaming-iptv.kcrsf.com/storage/updates/Electrofabiptv.apk')"
} else {
    Write-Host "ERROR: No se pudo generar el APK. Revisa los logs de arriba." -ForegroundColor Red
    exit 1
}

cd ..
