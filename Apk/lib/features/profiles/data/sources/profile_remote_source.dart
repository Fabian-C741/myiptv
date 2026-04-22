import 'dart:io';
import 'package:dio/dio.dart';
import '../../../../core/network/dio_client.dart';
import '../../../../core/config/app_config.dart';
import '../models/profile_model.dart';

class ProfileRemoteDataSource {
  final DioClient _dioClient;

  ProfileRemoteDataSource(this._dioClient);

  Future<List<ProfileModel>> getProfiles() async {
    try {
      final response = await _dioClient.instance.get(AppConfig.profiles);
      final List data = response.data;
      return data.map((json) => ProfileModel.fromJson(json)).toList();
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  Future<bool> verifyPin(int profileId, String pin) async {
    try {
      final response = await _dioClient.instance.post(
        '${AppConfig.profiles}/$profileId/verify-pin',
        data: {'pin': pin},
      );
      return response.data['success'] ?? false;
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  Future<ProfileModel> updateProfile(int id, Map<String, dynamic> data) async {
    try {
      final response = await _dioClient.instance.put('${AppConfig.profiles}/$id', data: data);
      return ProfileModel.fromJson(response.data);
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  Future<void> deleteProfile(int id) async {
    try {
      await _dioClient.instance.delete('${AppConfig.profiles}/$id');
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  Future<String> uploadAvatar(int profileId, File imageFile) async {
    try {
      final formData = FormData.fromMap({
        'avatar': await MultipartFile.fromFile(
          imageFile.path,
          filename: 'avatar_$profileId.jpg',
        ),
      });
      final response = await _dioClient.instance.post(
        '${AppConfig.profiles}/$profileId/avatar',
        data: formData,
        options: Options(contentType: 'multipart/form-data'),
      );
      return response.data['avatar_url'] as String;
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }

  String _handleError(DioException e) {
    if (e.response?.statusCode == 403) return 'PIN incorrecto';
    return 'Error al conectar con los perfiles';
  }
}

