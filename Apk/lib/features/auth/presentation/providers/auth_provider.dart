import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:device_info_plus/device_info_plus.dart';
import 'dart:io';

import '../../data/repositories/auth_repository_impl.dart';
import '../../data/sources/auth_remote_source.dart';
import '../../../../core/network/dio_client.dart';
import '../../../../core/storage/secure_storage_service.dart';
import '../../data/models/user_model.dart';

// Providers base
final dioClientProvider = Provider((ref) => DioClient(ref.watch(secureStorageProvider)));
final secureStorageProvider = Provider((ref) => SecureStorageService());

final authRepositoryProvider = Provider<AuthRepositoryImpl>((ref) {
  final dataSource = AuthRemoteDataSource(ref.watch(dioClientProvider));
  final storage = ref.watch(secureStorageProvider);
  return AuthRepositoryImpl(dataSource, storage);
});

// Estado de autenticación
class AuthState {
  final bool isLoading;
  final String? error;
  final UserModel? user;
  final bool isAuthenticated;

  AuthState({
    this.isLoading = false,
    this.error,
    this.user,
    this.isAuthenticated = false,
  });

  AuthState copyWith({
    bool? isLoading,
    String? error,
    UserModel? user,
    bool? isAuthenticated,
  }) {
    return AuthState(
      isLoading: isLoading ?? this.isLoading,
      error: error,
      user: user ?? this.user,
      isAuthenticated: isAuthenticated ?? this.isAuthenticated,
    );
  }
}

class AuthNotifier extends StateNotifier<AuthState> {
  final AuthRepositoryImpl _repository;

  AuthNotifier(this._repository) : super(AuthState());

  Future<void> login(String email, String password) async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final deviceInfo = await _getDeviceInfo();
      final user = await _repository.login(email, password, deviceInfo);
      state = state.copyWith(isLoading: false, user: user, isAuthenticated: true);
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
    }
  }

  Future<void> checkAuthStatus() async {
    final isLoggedIn = await _repository.isLoggedIn();
    if (isLoggedIn) {
      state = state.copyWith(isAuthenticated: true);
    }
  }

  Future<void> logout() async {
    await _repository.logout();
    state = AuthState();
  }

  Future<Map<String, dynamic>> _getDeviceInfo() async {
    final deviceInfoPlugin = DeviceInfoPlugin();
    if (Platform.isAndroid) {
      final androidInfo = await deviceInfoPlugin.androidInfo;
      return {
        'id': androidInfo.id,
        'name': androidInfo.model,
        'type': 'android_tv',
        'os': 'Android ${androidInfo.version.release}',
      };
    } else {
      return {
        'id': 'unknown_id',
        'name': 'Generic Device',
        'type': 'mobile',
        'os': Platform.operatingSystem,
      };
    }
  }
}

final authProvider = StateNotifierProvider<AuthNotifier, AuthState>((ref) {
  return AuthNotifier(ref.watch(authRepositoryProvider));
});
