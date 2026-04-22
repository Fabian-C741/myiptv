import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:ott_app/core/config/app_config.dart';
import 'package:ott_app/shared/models/channel_model.dart';
import 'package:ott_app/features/auth/presentation/providers/auth_provider.dart';

class BrandConfig {
  final String appName;
  final String? logoUrl;
  final String currentVersion;
  final String? apkUrl;

  BrandConfig({
    required this.appName,
    this.logoUrl,
    required this.currentVersion,
    this.apkUrl,
  });

  factory BrandConfig.fromJson(Map<String, dynamic> json) {
    return BrandConfig(
      appName: json['app_name'] ?? 'ELECTROFABI IPTV',
      logoUrl: json['app_logo'],
      currentVersion: json['current_version'] ?? '1.0.0',
      apkUrl: json['apk_url'],
    );
  }
}

class BrandState {
  final BrandConfig? config;
  final bool isLoading;
  final String? error;

  BrandState({this.config, this.isLoading = false, this.error});

  BrandState copyWith({BrandConfig? config, bool? isLoading, String? error}) {
    return BrandState(
      config: config ?? this.config,
      isLoading: isLoading ?? this.isLoading,
      error: error,
    );
  }
}

class BrandNotifier extends StateNotifier<BrandState> {
  final Ref _ref;
  BrandNotifier(this._ref) : super(BrandState()) {
    fetchConfig();
  }

  Future<void> fetchConfig() async {
    state = state.copyWith(isLoading: true);
    try {
      final dio = _ref.read(dioClientProvider);
      final response = await dio.instance.get(AppConfig.brandConfig);
      
      if (response.statusCode == 200) {
        state = state.copyWith(
          config: BrandConfig.fromJson(response.data),
          isLoading: false,
        );
      }
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
    }
  }
}

final brandProvider = StateNotifierProvider<BrandNotifier, BrandState>((ref) {
  return BrandNotifier(ref);
});
