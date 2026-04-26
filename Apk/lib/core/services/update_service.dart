import 'dart:io';
import 'package:flutter/material.dart';
import 'package:package_info_plus/package_info_plus.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:dio/dio.dart';
import '../network/dio_client.dart';
import '../config/app_config.dart';
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
          debugPrint('⏱️ [UpdateService] Timeout - servidor no responde');
          throw Exception('Timeout');
        },
      );
      
      debugPrint('📡 [UpdateService] Respuesta: ${response.statusCode}');
      
      if (response.statusCode == 200 && context.mounted) {
        // Forzamos conversión a String por si el JSON viene con formatos inesperados
        final serverVersion = response.data['current_version']?.toString();
        final apkUrl = response.data['apk_url']?.toString();

        debugPrint('🖥️ [UpdateService] Versión servidor: $serverVersion');
        debugPrint('🔗 [UpdateService] APK URL: $apkUrl');

        if (serverVersion == null || apkUrl == null || apkUrl.isEmpty) {
          debugPrint('⚠️ [UpdateService] Datos incompletos');
          return;
        }

        final packageInfo = await PackageInfo.fromPlatform();
        final currentVersion = packageInfo.version;

        debugPrint('📱 [UpdateService] Versión actual app: $currentVersion');

        if (_isVersionGreater(serverVersion, currentVersion)) {
          debugPrint('✅ [UpdateService] Nueva versión disponible!');
          await _showUpdateDialog(context, serverVersion, apkUrl);
        } else {
          debugPrint('✅ [UpdateService] App actualizada');
        }
      }
    } on DioException catch (e) {
      debugPrint('❌ [UpdateService] Error de conexión: ${e.message}');
      _showErrorSnackBar(context, 'Sin conexión al servidor de actualizaciones');
    } catch (e) {
      debugPrint('❌ [UpdateService] Error: $e');
    }
  }

  bool _isVersionGreater(String server, String local) {
    try {
      // Limpiamos versiones de cualquier cosa que no sea números o puntos (ej: 1.0.3+4 -> 1.0.3)
      final cleanServer = server.split('+')[0].split('-')[0];
      final cleanLocal = local.split('+')[0].split('-')[0];
      
      List<String> sParts = cleanServer.split('.');
      List<String> lParts = cleanLocal.split('.');
      int length = sParts.length > lParts.length ? sParts.length : lParts.length;
      
      debugPrint('📊 [UpdateService] Comparando: $server vs $local');
      
      for (int i = 0; i < length; i++) {
        int s = i < sParts.length ? int.tryParse(sParts[i].replaceAll(RegExp(r'[^0-9]'), '')) ?? 0 : 0;
        int l = i < lParts.length ? int.tryParse(lParts[i].replaceAll(RegExp(r'[^0-9]'), '')) ?? 0 : 0;
        
        if (s > l) {
          debugPrint('🔺 [UpdateService] Servidor mayor en posición $i: $s > $l');
          return true;
        }
        if (s < l) {
          debugPrint('🔻 [UpdateService] Local mayor en posición $i: $l > $s');
          return false;
        }
      }
    } catch (e) {
      debugPrint('❌ [UpdateService] Error comparando: $e');
      return server != local;
    }
    return false;
  }

  Future<void> _showUpdateDialog(BuildContext context, String version, String url) async {
    await showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        backgroundColor: const Color(0xFF1A1A1E),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Row(
          children: [
            const Icon(Icons.system_update, color: AppTheme.primaryRed),
            const SizedBox(width: 12),
            const Text('Nueva Versión', style: TextStyle(color: Colors.white)),
          ],
        ),
        content: Text(
          '¡Electrofabiptv v$version ya está disponible! Actualiza ahora para disfrutar de las últimas mejoras y mayor seguridad.',
          style: const TextStyle(color: Colors.grey),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('IGNORAR', style: TextStyle(color: Colors.white24, fontWeight: FontWeight.bold)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.primaryRed,
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            onPressed: () {
              Navigator.pop(context);
              _launchURL(url);
            },
            child: const Text('ACTUALIZAR AHORA', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  void _showErrorSnackBar(BuildContext context, String message) {
    if (context.mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(message),
          backgroundColor: Colors.orange,
          duration: const Duration(seconds: 3),
        ),
      );
    }
  }

  Future<void> _launchURL(String url) async {
    try {
      debugPrint('🚀 [UpdateService] Abriendo URL: $url');
      final uri = Uri.parse(url);
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    } catch (e) {
      debugPrint('❌ [UpdateService] Error al abrir URL: $e');
    }
  }
}
