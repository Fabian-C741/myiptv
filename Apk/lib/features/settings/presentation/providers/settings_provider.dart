import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/network/dio_client.dart';
import '../../../../core/config/app_config.dart';
import '../../../../features/auth/presentation/providers/auth_provider.dart';
import 'package:ott_app/features/settings/data/models/device_model.dart';

class SettingsRemoteDataSource {
  final DioClient _dioClient;

  SettingsRemoteDataSource(this._dioClient);

  Future<List<DeviceModel>> getDevices() async {
    try {
      final response = await _dioClient.instance.get(AppConfig.devices);
      final List data = response.data;
      return data.map((json) => DeviceModel.fromJson(json)).toList();
    } on DioException catch (_) {
      throw 'Error al obtener dispositivos';
    }
  }

  Future<void> revokeDevice(int deviceId) async {
    try {
      await _dioClient.instance.delete('${AppConfig.devices}/$deviceId');
    } on DioException catch (_) {
      throw 'Error al eliminar dispositivo';
    }
  }

  Future<void> updateProfile(int profileId, Map<String, dynamic> data) async {
    try {
      await _dioClient.instance.post('${AppConfig.profiles}/$profileId', data: data);
    } on DioException catch (_) {
      throw 'Error al actualizar perfil';
    }
  }
}

final settingsDataSourceProvider = Provider((ref) {
  return SettingsRemoteDataSource(ref.watch(dioClientProvider));
});

class SettingsState {
  final List<DeviceModel> devices;
  final bool isLoading;
  final String? error;

  SettingsState({this.devices = const [], this.isLoading = false, this.error});

  SettingsState copyWith({List<DeviceModel>? devices, bool? isLoading, String? error}) {
    return SettingsState(
      devices: devices ?? this.devices,
      isLoading: isLoading ?? this.isLoading,
      error: error,
    );
  }
}

class SettingsNotifier extends StateNotifier<SettingsState> {
  final SettingsRemoteDataSource _dataSource;

  SettingsNotifier(this._dataSource) : super(SettingsState()) {
    loadDevices();
  }

  Future<void> loadDevices() async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final devices = await _dataSource.getDevices();
      state = state.copyWith(isLoading: false, devices: devices);
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
    }
  }

  Future<void> revokeDevice(int deviceId) async {
    try {
      await _dataSource.revokeDevice(deviceId);
      await loadDevices();
    } catch (e) {
      state = state.copyWith(error: e.toString());
    }
  }
}

final settingsProvider = StateNotifierProvider<SettingsNotifier, SettingsState>((ref) {
  return SettingsNotifier(ref.watch(settingsDataSourceProvider));
});
