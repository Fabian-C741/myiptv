import os, shutil
from google.colab import files

# 1. Limpieza y clonación de repositorio
%cd /content
if os.path.exists('myiptv'):
    shutil.rmtree('myiptv', ignore_errors=True)
!git clone https://github.com/Fabian-C741/myiptv.git
if not os.path.exists('myiptv'):
    print("❌ ERROR: Fallo al clonar el repositorio")
    raise SystemExit(1)

# 2. Configurar ruta correcta del proyecto
%cd /content/myiptv
if os.path.exists('Apk/pubspec.yaml'):
    project_path = '/content/myiptv/Apk'
    %cd $project_path
elif os.path.exists('pubspec.yaml'):
    project_path = '/content/myiptv'
    %cd $project_path
else:
    print("❌ ERROR: No se encontró pubspec.yaml")
    raise SystemExit(1)

print(f"✅ Ruta del proyecto: {project_path}")

# 3. Configurar keystore PKCS12 con ruta absoluta
keystore_path = os.path.join(project_path, 'android/app/upload-keystore.p12')
key_props = f"""
storePassword=El3ctroF4b1kcrsM
keyPassword=El3ctroF4b1kcrsM
keyAlias=upload
storeFile={keystore_path}
"""
key_props_path = os.path.join(project_path, 'android/key.properties')
with open(key_props_path, 'w') as f:
    f.write(key_props)
print(f"✅ Creado {key_props_path}")

# 4. Generar keystore
print("🔑 Generando keystore...")
!keytool -genkey -v \
  -keystore "{keystore_path}" \
  -storetype PKCS12 \
  -keyalg RSA \
  -keysize 2048 \
  -validity 10000 \
  -alias upload \
  -storepass El3ctroF4b1kcrsM \
  -keypass El3ctroF4b1kcrsM \
  -dname "CN=ElectroFabi, O=ElectroFabiIPTV, C=AR" \
  -noprompt

if not os.path.exists(keystore_path):
    print("❌ ERROR: Fallo al generar keystore")
    raise SystemExit(1)
print(f"✅ Keystore generado en {keystore_path}")

# 5. Configurar NDK 27 requerido por media_kit
bg_path = os.path.join(project_path, 'android/app/build.gradle')
if os.path.exists(bg_path):
    with open(bg_path, 'r') as f:
        content = f.read()
    if 'ndkVersion' not in content:
        content = content.replace('android {', 'android {\n    ndkVersion "27.0.12077973"')
        with open(bg_path, 'w') as f:
            f.write(content)
        print("✅ Agregada ndkVersion 27.0.12077973")
    else:
        print("✅ ndkVersion ya existe")

# 6. Corregir código roto
hs_path = os.path.join(project_path, 'lib/features/home/presentation/screens/home_screen.dart')
if os.path.exists(hs_path):
    with open(hs_path, 'r') as f:
        content = f.read()
    content = content.replace("const _FilterButton", "// const _FilterButton")
    content = content.replace("const _FilterChip", "// const _FilterChip")
    with open(hs_path, 'w') as f:
        f.write(content)
    print("✅ Código corregido en home_screen.dart")

# 7. Instalar Flutter 3.24.5
if os.path.exists('/content/flutter'):
    shutil.rmtree('/content/flutter', ignore_errors=True)

print("⬇️ Descargando Flutter 3.24.5...")
!wget -q https://storage.googleapis.com/flutter_infra_release/releases/stable/linux/flutter_linux_3.24.5-stable.tar.xz
!tar xf /content/flutter_linux_3.24.5-stable.tar.xz -C /content/
os.environ['PATH'] += ":/content/flutter/bin"
!flutter --version

# 8. flutter pub get
print("\n⬇️ Ejecutando flutter pub get...")
pub_get_log = '/content/pub_get.log'
!flutter pub get 2>&1 | tee {pub_get_log}
with open(pub_get_log, 'r') as f:
    log = f.read()
if 'Dependencies are now resolved' not in log and 'Changed' not in log:
    print(f"❌ ERROR en pub get. Revisa {pub_get_log}")
    print(log[-500:])
    raise SystemExit(1)
print("✅ flutter pub get completado")

# 9. Compilar APK
print("\n🔨 Compilando APK...")
build_log = '/content/build.log'
!flutter build apk --release --target-platform android-arm64 --no-tree-shake-icons 2>&1 | tee {build_log}

source_apk = os.path.join(project_path, 'build/app/outputs/flutter-apk/app-arm64-v8a-release.apk')
if not os.path.exists(source_apk):
    print(f"❌ ERROR: No se generó APK. Revisa {build_log}")
    with open(build_log, 'r') as f:
        print(f.read()[-1000:])
    raise SystemExit(1)

# 10. Descargar APK
shutil.copy(source_apk, '/content/Electrofabiptv.apk')
print("✅ APK renombrado a Electrofabiptv.apk")
print("✅ Iniciando descarga...")
files.download('/content/Electrofabiptv.apk')
