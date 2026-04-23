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

  /**
   * Verifica si hay una nueva versión disponible en el servidor.
   */
  Future<void> checkForUpdates(BuildContext context) async {
    try {
      // Timeout de 3 segundos para no bloquear el inicio
      final response = await _dio.instance.get('/app/config').timeout(
        const Duration(seconds: 3),
        onTimeout: () => throw Exception('Timeout'),
      );
      
      if (response.statusCode == 200 && context.mounted) {
        final serverVersion = response.data['current_version'];
        final apkUrl = response.data['apk_url'];

        if (serverVersion == null || apkUrl == null) return;

        final packageInfo = await PackageInfo.fromPlatform();
        final currentVersion = packageInfo.version;

        if (_isVersionGreater(serverVersion, currentVersion)) {
          _showUpdateDialog(context, serverVersion, apkUrl);
        }
      }
    } catch (e) {
      debugPrint('Error en update check: $e');
    }
  }

  bool _isVersionGreater(String server, String local) {
    try {
      List<String> sParts = server.split('.');
      List<String> lParts = local.split('.');
      int length = sParts.length > lParts.length ? sParts.length : lParts.length;
      for (int i = 0; i < length; i++) {
        int s = i < sParts.length ? int.parse(sParts[i].replaceAll(RegExp(r'[^0-9]'), '')) : 0;
        int l = i < lParts.length ? int.parse(lParts[i].replaceAll(RegExp(r'[^0-9]'), '')) : 0;
        if (s > l) return true;
        if (s < l) return false;
      }
    } catch (e) {
      return server != local;
    }
    return false;
  }

  void _showUpdateDialog(BuildContext context, String version, String url) {
    showDialog(
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
            onPressed: () => _launchURL(url),
            child: const Text('ACTUALIZAR AHORA', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  Future<void> _launchURL(String url) async {
    try {
      final uri = Uri.parse(url);
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    } catch (e) {
      // Error silencioso
    }
  }
}
