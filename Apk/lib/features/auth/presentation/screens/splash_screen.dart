import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:package_info_plus/package_info_plus.dart';
import '../providers/auth_provider.dart';
import '../../../../core/services/update_service.dart';
import '../../../../core/network/dio_client.dart';
import '../../../../core/storage/secure_storage_service.dart';
import '../../../../core/theme/app_theme.dart';

class SplashScreen extends ConsumerStatefulWidget {
  const SplashScreen({super.key});

  @override
  ConsumerState<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends ConsumerState<SplashScreen> {
  String _versionInfo = '';

  @override
  void initState() {
    super.initState();
    _init();
  }

  Future<void> _init() async {
    try {
      await Future.delayed(const Duration(milliseconds: 500));
      
      if (!mounted) return;
      
      final packageInfo = await PackageInfo.fromPlatform();
      setState(() => _versionInfo = 'v${packageInfo.version}');
      
      final dio = DioClient(SecureStorageService());
      await AppUpdateService(dio).checkForUpdates(context);
      
      if (!mounted) return;

      await ref.read(authProvider.notifier).checkAuthStatus();
      
      if (mounted) {
        final authState = ref.read(authProvider);
        if (authState.isAuthenticated) {
          context.go('/profiles');
        } else {
          context.go('/login');
        }
      }
    } catch (e) {
      if (mounted) context.go('/login');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.play_circle_fill, size: 100, color: AppTheme.primaryRed),
            const SizedBox(height: 20),
            Text(_versionInfo, style: const TextStyle(color: Colors.white54, fontSize: 12)),
          ],
        ),
      ),
    );
  }
}