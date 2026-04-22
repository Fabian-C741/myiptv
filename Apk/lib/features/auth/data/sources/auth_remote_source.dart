import 'package:dio/dio.dart';
import '../../../../core/network/dio_client.dart';
import '../../../../core/config/app_config.dart';
import '../models/user_model.dart';

class AuthRemoteDataSource {
  final DioClient _dioClient;

  AuthRemoteDataSource(this._dioClient);

  Future<Map<String, dynamic>> login({
    required String email,
    required String password,
    required Map<String, dynamic> deviceInfo,
  }) async {
    try {
      final response = await _dioClient.instance.post(
        AppConfig.login,
        data: {
          'email': email,
          'password': password,
          'device_id': deviceInfo['id'],
          'device_name': deviceInfo['name'],
          'device_type': deviceInfo['type'],
        },
      );
      return response.data;
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  Future<void> logout() async {
    try {
      await _dioClient.instance.post(AppConfig.logout);
    } catch (_) {}
  }

  String _handleError(DioException e) {
    if (e.response?.statusCode == 422) {
      return e.response?.data['message'] ?? 'Credenciales inválidas';
    }
    if (e.response?.statusCode == 429) {
      return 'Límite de dispositivos excedido';
    }
    return 'Error de conexión con el servidor';
  }
}
