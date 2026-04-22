import 'dart:io';
import 'package:flutter/material.dart';
import 'package:package_info_plus/package_info_plus.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:dio/dio.dart';
import '../network/dio_client.dart';
import '../config/app_config.dart';

class AppUpdateService {
  final DioClient _dio;

  AppUpdateService(this._dio);

  /**
   * Verifica si hay una nueva versión disponible en el servidor.
   */
  Future<void> checkForUpdates(BuildContext context) async {
    try {
      // Usamos /app/config que ahora es pública
      final response = await _dio.instance.get('/app/config');
      if (response.statusCode == 200) {
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
      // Silencioso en caso de error de red durante el check inicial
    }
  }

  bool _isVersionGreater(String server, String local) {
    List<int> s = server.split('.').map(int.parse).toList();
    List<int> l = local.split('.').map(int.parse).toList();
    
    for (int i = 0; i < s.length; i++) {
      if (s[i] > l[i]) return true;
      if (s[i] < l[i]) return false;
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
            const Icon(Icons.system_update, color: Colors.blueAccent),
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
            child: const Text('Más tarde', style: TextStyle(color: Colors.grey)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.blueAccent,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            onPressed: () => _launchURL(url),
            child: const Text('Actualizar Ahora'),
          ),
        ],
      ),
    );
  }

  Future<void> _launchURL(String url) async {
    final uri = Uri.parse(url);
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    }
  }
}
