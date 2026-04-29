import 'dart:io';
import 'package:flutter/material.dart';
import 'package:package_info_plus/package_info_plus.dart';
import 'package:dio/dio.dart';
import 'package:path_provider/path_provider.dart';
import 'package:open_file/open_file.dart';
import '../network/dio_client.dart';
import '../../core/theme/app_theme.dart';

class AppUpdateService {
  final DioClient _dio;

  AppUpdateService(this._dio);

  Future<void> checkForUpdates(BuildContext context) async {
    try {
      debugPrint('🔄 [UpdateService] Verificando actualizaciones...');

      final response = await _dio.instance.get('/app/config').timeout(
        const Duration(seconds: 10),
        onTimeout: () {
          debugPrint('⏱️ [UpdateService] Timeout');
          throw Exception('Timeout');
        },
      );

      if (response.statusCode == 200 && context.mounted) {
        final serverVersion = response.data['current_version']?.toString();
        final apkUrl = response.data['apk_url']?.toString();

        debugPrint('🖥️ [UpdateService] Servidor: $serverVersion | URL: $apkUrl');

        if (serverVersion == null || apkUrl == null || apkUrl.isEmpty) {
          debugPrint('⚠️ [UpdateService] Datos incompletos');
          return;
        }

        final packageInfo = await PackageInfo.fromPlatform();
        final currentVersion = packageInfo.version;
        debugPrint('📱 [UpdateService] App actual: $currentVersion');

        if (_isVersionGreater(serverVersion, currentVersion)) {
          debugPrint('✅ [UpdateService] Actualización disponible!');
          if (context.mounted) {
            await _showUpdateDialog(context, serverVersion, apkUrl);
          }
        } else {
          debugPrint('✅ [UpdateService] App al día');
        }
      }
    } on DioException catch (e) {
      debugPrint('❌ [UpdateService] Error de red: ${e.message}');
    } catch (e) {
      debugPrint('❌ [UpdateService] Error: $e');
    }
  }

  bool _isVersionGreater(String server, String local) {
    try {
      // Limpiamos versiones (quitamos el + y cualquier letra)
      final cleanServer = server.split('+')[0].trim();
      final cleanLocal = local.split('+')[0].trim();
      
      // Si son iguales, no es mayor
      if (cleanServer == cleanLocal) return false;

      final sParts = cleanServer.split('.').map((e) => int.tryParse(e) ?? 0).toList();
      final lParts = cleanLocal.split('.').map((e) => int.tryParse(e) ?? 0).toList();

      for (int i = 0; i < 3; i++) {
        final s = i < sParts.length ? sParts[i] : 0;
        final l = i < lParts.length ? lParts[i] : 0;
        if (s > l) return true;
        if (s < l) return false;
      }
    } catch (e) {
      return false;
    }
    return false;
  }

  Future<void> _showUpdateDialog(BuildContext context, String version, String url) async {
    await showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => AlertDialog(
        backgroundColor: const Color(0xFF1A1A1E),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(Icons.system_update, color: AppTheme.primaryRed),
            SizedBox(width: 12),
            Text('Nueva Versión', style: TextStyle(color: Colors.white)),
          ],
        ),
        content: Text(
          '¡Electrofabiptv v$version ya está disponible!\nSe descargará e instalará automáticamente.',
          style: const TextStyle(color: Colors.grey),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('AHORA NO', style: TextStyle(color: Colors.white24, fontWeight: FontWeight.bold)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.primaryRed,
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            onPressed: () {
              Navigator.pop(ctx);
              _downloadAndInstall(context, url, version);
            },
            child: const Text('ACTUALIZAR', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  /// Descarga el APK mostrando progreso y luego lanza el instalador nativo
  Future<void> _downloadAndInstall(BuildContext context, String url, String version) async {
    // Mostramos el diálogo de progreso
    double progress = 0;
    bool cancelled = false;
    CancelToken cancelToken = CancelToken();

    if (!context.mounted) return;

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setStateDialog) => AlertDialog(
          backgroundColor: const Color(0xFF1A1A1E),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: const Text('Descargando actualización...', style: TextStyle(color: Colors.white, fontSize: 16)),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const SizedBox(height: 8),
              LinearProgressIndicator(
                value: progress,
                backgroundColor: Colors.white12,
                valueColor: const AlwaysStoppedAnimation<Color>(AppTheme.primaryRed),
                minHeight: 8,
                borderRadius: BorderRadius.circular(4),
              ),
              const SizedBox(height: 12),
              Text(
                '${(progress * 100).toStringAsFixed(0)}%',
                style: const TextStyle(color: Colors.white70, fontSize: 14),
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () {
                cancelled = true;
                cancelToken.cancel('Cancelado por el usuario');
                Navigator.pop(ctx);
              },
              child: const Text('CANCELAR', style: TextStyle(color: Colors.white38)),
            ),
          ],
        ),
      ),
    );

    try {
      // Guardamos el APK en la carpeta temporal del sistema
      final dir = await getExternalStorageDirectory() ?? await getTemporaryDirectory();
      final savePath = '${dir.path}/ElectrofabUpdate_$version.apk';
      final file = File(savePath);
      if (await file.exists()) await file.delete();

      // Descargamos
      final downloadDio = Dio();
      await downloadDio.download(
        url,
        savePath,
        cancelToken: cancelToken,
        onReceiveProgress: (received, total) {
          if (total > 0) {
            progress = received / total;
            // Actualizar el diálogo de progreso
            (context as Element).markNeedsBuild();
          }
        },
      );

      if (cancelled) return;

      // Cerramos el diálogo de progreso
      if (context.mounted) Navigator.of(context, rootNavigator: true).pop();

      // Instalamos directamente usando Android Intent
      await _installApk(savePath);
    } catch (e) {
      if (cancelled) return;
      debugPrint('❌ Error descargando: $e');
      if (context.mounted) {
        Navigator.of(context, rootNavigator: true).pop();
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Error al descargar. Inténtalo de nuevo.'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  Future<void> _installApk(String filePath) async {
    try {
      debugPrint('🚀 Lanzando instalador nativo para: $filePath');
      final result = await OpenFile.open(filePath, type: 'application/vnd.android.package-archive');
      if (result.type != ResultType.done) {
        debugPrint('❌ No se pudo abrir el instalador: ${result.message}');
      }
    } catch (e) {
      debugPrint('❌ Error crítico instalando APK: $e');
    }
  }
}
