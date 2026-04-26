$ErrorActionPreference = "Continue"
$FLUTTER  = "D:\Apk-tv\flutter_portable\bin\flutter.bat"
$APK_DIR  = "D:\Apk-tv\Apk"
$ANDROID  = "D:\Apk-tv\Apk\android"

Write-Host "=== [1/6] Matando procesos Java/Gradle ===" -ForegroundColor Cyan
Get-Process -Name "java","javaw","gradle" -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
Start-Sleep -Seconds 2

Write-Host "=== [2/6] Limpiando caches Gradle ===" -ForegroundColor Cyan
$cacheDirs = @(
    "$env:USERPROFILE\.gradle\caches\transforms-3",
    "$env:USERPROFILE\.gradle\caches\transforms-4",
    "$env:USERPROFILE\.gradle\daemon",
    "$env:USERPROFILE\.gradle\workers",
    "$ANDROID\.gradle",
    "$ANDROID\.kotlin",
    "$APK_DIR\build",
    "$APK_DIR\.dart_tool"
)
foreach ($d in $cacheDirs) {
    if (Test-Path $d) {
        Write-Host "  Borrando $d ..." -ForegroundColor DarkGray
        Remove-Item -Recurse -Force $d -ErrorAction SilentlyContinue
    }
}

Write-Host "=== [3/6] Actualizando local.properties ===" -ForegroundColor Cyan
$lp = "sdk.dir=$($env:USERPROFILE.Replace('\','\\'))\\AppData\\Local\\Android\\sdk`nflutter.sdk=D:\\Apk-tv\\flutter_portable`nflutter.versionName=1.0.0`nflutter.versionCode=1"
Set-Content "$ANDROID\local.properties" $lp -Encoding UTF8

Write-Host "=== [4/6] Flutter pub get ===" -ForegroundColor Cyan
Set-Location $APK_DIR
& $FLUTTER pub get

Write-Host "=== [4.5/6] Generando Iconos de la App ===" -ForegroundColor Cyan
& $FLUTTER pub run flutter_launcher_icons

Write-Host "=== [5/6] Building APK Release ===" -ForegroundColor Cyan
$env:GRADLE_OPTS = "-Xmx4096m -Dorg.gradle.daemon=false"
& $FLUTTER build apk --release --split-per-abi 2>&1 | Tee-Object "D:\Apk-tv\build_output.log"

Write-Host "=== [6/6] Buscando APK generado ===" -ForegroundColor Cyan
$apks = Get-ChildItem "$APK_DIR\build\app\outputs" -Recurse -Filter "*.apk" -ErrorAction SilentlyContinue
if ($apks) {
    foreach ($apk in $apks) {
        $sz = [math]::Round($apk.Length/1MB,2)
        Write-Host "  ENCONTRADO: $($apk.FullName) ($sz MB)" -ForegroundColor Green
        Copy-Item $apk.FullName "D:\Apk-tv\$($apk.Name)" -Force
        Write-Host "  Copiado a: D:\Apk-tv\$($apk.Name)" -ForegroundColor Green
    }
} else {
    Write-Host "  BUILD FALLO - revisa D:\Apk-tv\build_output.log" -ForegroundColor Red
    Get-Content "D:\Apk-tv\build_output.log" | Select-Object -Last 40
}
