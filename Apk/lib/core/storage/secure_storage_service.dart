import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class SecureStorageService {
  final FlutterSecureStorage _storage = const FlutterSecureStorage();

  static const _keyToken = 'auth_token';
  static const _keyProfileId = 'selected_profile_id';
  static const _keyDeviceId = 'device_unique_id';

  Future<void> saveToken(String token) async {
    await _storage.write(key: _keyToken, value: token);
  }

  Future<String?> getToken() async {
    return await _storage.read(key: _keyToken);
  }

  Future<void> deleteToken() async {
    await _storage.delete(key: _keyToken);
  }

  Future<void> saveProfileId(int id) async {
    await _storage.write(key: _keyProfileId, value: id.toString());
  }

  Future<int?> getProfileId() async {
    String? id = await _storage.read(key: _keyProfileId);
    return id != null ? int.tryParse(id) : null;
  }

  Future<void> saveDeviceId(String deviceId) async {
    await _storage.write(key: _keyDeviceId, value: deviceId);
  }

  Future<String?> getDeviceId() async {
    return await _storage.read(key: _keyDeviceId);
  }

  Future<void> clearAll() async {
    await _storage.deleteAll();
  }
}
