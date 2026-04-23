import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../network/dio_client.dart';
import '../storage/secure_storage_service.dart';

class BrandState {
  final String name;
  final String? logoUrl;
  final String version;
  final bool isLoading;

  BrandState({
    this.name = 'ELECTROFABI IPTV',
    this.logoUrl,
    this.version = '1.0.0',
    this.isLoading = true,
  });

  BrandState copyWith({String? name, String? logoUrl, String? version, bool? isLoading}) {
    return BrandState(
      name: name ?? this.name,
      logoUrl: logoUrl ?? this.logoUrl,
      version: version ?? this.version,
      isLoading: isLoading ?? this.isLoading,
    );
  }
}

class BrandNotifier extends StateNotifier<BrandState> {
  final DioClient _dio;
  BrandNotifier(this._dio) : super(BrandState()) {
    fetchBrandConfig();
  }

  Future<void> fetchBrandConfig() async {
    try {
      final response = await _dio.instance.get('/app/config');
      if (response.statusCode == 200) {
        state = state.copyWith(
          name: response.data['app_name'] ?? 'ELECTROFABI IPTV',
          logoUrl: response.data['app_logo'],
          version: response.data['current_version'] ?? '1.0.0',
          isLoading: false,
        );
      }
    } catch (e) {
      state = state.copyWith(isLoading: false);
    }
  }
}

final brandProvider = StateNotifierProvider<BrandNotifier, BrandState>((ref) {
  final dio = DioClient(SecureStorageService());
  return BrandNotifier(dio);
});
