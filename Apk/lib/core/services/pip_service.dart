import 'package:flutter/services.dart';
import 'package:flutter/foundation.dart';

/// Servicio para controlar el modo Picture-in-Picture (PiP)
/// Solo funciona en Android 8.0 (API 26) o superior.
class PipService {
  static const _channel = MethodChannel('com.electrofabiptv.app/pip');

  static Function(bool)? _onPipModeChanged;

  /// Inicializar el listener de cambios de modo PiP
  static void initialize({Function(bool isInPipMode)? onPipModeChanged}) {
    _onPipModeChanged = onPipModeChanged;
    _channel.setMethodCallHandler(_handleMethodCall);
  }

  static Future<dynamic> _handleMethodCall(MethodCall call) async {
    switch (call.method) {
      case 'onPipModeChanged':
        final isInPipMode = call.arguments['isInPipMode'] as bool? ?? false;
        _onPipModeChanged?.call(isInPipMode);
        break;
      case 'onUserLeaveHint':
        // La app nativa nos avisa que el usuario presionó Home.
        // Dejamos que el reproductor decida si entrar en PiP.
        _onPipModeChanged?.call(false); // No en PiP todavía
        break;
    }
  }

  /// Entrar en modo PiP. Retorna true si fue exitoso.
  static Future<bool> enterPip() async {
    try {
      final result = await _channel.invokeMethod<bool>('enterPip');
      return result ?? false;
    } catch (e) {
      debugPrint('❌ PipService.enterPip error: $e');
      return false;
    }
  }

  /// Verificar si PiP está soportado en el dispositivo.
  static Future<bool> isPipSupported() async {
    try {
      final result = await _channel.invokeMethod<bool>('isPipSupported');
      return result ?? false;
    } catch (e) {
      return false;
    }
  }

  /// Verificar si actualmente estamos en modo PiP.
  static Future<bool> isInPipMode() async {
    try {
      final result = await _channel.invokeMethod<bool>('isInPipMode');
      return result ?? false;
    } catch (e) {
      return false;
    }
  }
}
