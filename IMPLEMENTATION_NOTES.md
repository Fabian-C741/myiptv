# Registro de Implementaciones - ElectroFabi IPTV (Abril 2026)

Este documento detalla los cambios técnicos realizados para estabilizar la plataforma, optimizar el rendimiento y automatizar los despliegues.

## 1. Sistema de Video (Player)
Se realizaron ajustes críticos para mejorar la experiencia del usuario final:
- **Baja Latencia:** Se redujo el buffer inicial de 10MB a **2MB** en `player_provider.dart`. Esto permite que los canales abran instantáneamente.
- **Autoplay Forzado:** Se implementó una lógica de `Future.delayed` con un `play()` explícito para asegurar que el video arranque solo al abrir la pantalla, sin necesidad de interacción manual.
- **Optimización de Streaming:** Se configuraron las propiedades `network-timeout=10` y `cache-pause=no` en el motor `media_kit` para evitar cortes constantes en conexiones inestables.
- **Picture-in-Picture (PiP):** Se habilitó el soporte nativo en `AndroidManifest.xml` y se configuró la App para que el video continúe en una ventana flotante al salir.

## 2. Automatización y Despliegue (CI/CD)
Se eliminó la necesidad de subir archivos por FTP manualmente:
- **Auto-Deploy por Cron:** Se creó el script `public/cron_deploy.php` que revisa GitHub cada 5 minutos. Si hay cambios, ejecuta `git pull` y limpia la caché de Laravel automáticamente.
- **Ruta del Proyecto:** `/home/u496356948/domains/streaming-iptv.kcrsf.com/public_html/`.
- **Detección Automática de Versión:** El backend (`ConfigController.php`) ahora extrae la versión directamente del binario del APK subido (`AndroidManifest.xml`). Ya no es necesario escribir la versión a mano en el panel admin.

## 3. Backend y API
- **Soporte VOD Paginado:** Se corrigió `LiveTvRemoteDataSource` para que pueda leer tanto listas simples como respuestas paginadas de Laravel (`{data: [...]}`). Esto solucionó el error de "Series y Películas no cargan".
- **Gestión de Usuarios Bloqueados:** 
    - El servidor ahora devuelve un mensaje claro: *"Cuenta suspendida por falta de pago. Contacta a soporte para reactivar tu servicio."*
    - La App detecta este error y muestra un botón de **"¿Necesitas ayuda?"** que abre WhatsApp directamente con el número de soporte configurado.

## 4. Guía de Compilación (Google Colab)
Para mantener la integridad del proyecto, el proceso de compilación debe seguir estas reglas:
- **Repositorio:** `https://github.com/Fabian-C741/myiptv.git`
- **Carpeta del Proyecto:** `/Apk`
- **Nombre del Archivo:** Siempre debe ser **`Electrofabiptv.apk`**.
- **Arquitectura:** Se compila específicamente para `android-arm64` para optimizar el peso del archivo (pasó de 103MB a ~18MB).

## 5. Estructura de Carpetas
- `/home/u496356948/` -> Carpeta raíz del servidor.
- `/public_html/` -> Contiene el Backend Laravel y los scripts de despliegue.
- `/Apk/` -> Contiene el código fuente de la App Flutter.

---
**Desarrollado por Antigravity (Google DeepMind Team) para Fabian-C741.**
