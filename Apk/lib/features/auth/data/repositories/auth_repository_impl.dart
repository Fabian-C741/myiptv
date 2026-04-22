import '../sources/auth_remote_source.dart';
import '../models/user_model.dart';
import '../../../../core/storage/secure_storage_service.dart';

abstract class AuthRepository {
  Future<UserModel> login(String email, String password, Map<String, dynamic> deviceInfo);
  Future<void> logout();
  Future<bool> isLoggedIn();
}

class AuthRepositoryImpl implements AuthRepository {
  final AuthRemoteDataSource _remoteDataSource;
  final SecureStorageService _storage;

  AuthRepositoryImpl(this._remoteDataSource, this._storage);

  @override
  Future<UserModel> login(String email, String password, Map<String, dynamic> deviceInfo) async {
    final data = await _remoteDataSource.login(
      email: email,
      password: password,
      deviceInfo: deviceInfo,
    );
    
    final token = data['token'];
    final user = UserModel.fromJson(data['user']);
    
    await _storage.saveToken(token);
    return user;
  }

  @override
  Future<void> logout() async {
    await _remoteDataSource.logout();
    await _storage.clearAll();
  }

  @override
  Future<bool> isLoggedIn() async {
    final token = await _storage.getToken();
    return token != null;
  }
}
