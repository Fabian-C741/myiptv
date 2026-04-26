import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
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
  @override
  void initState() {
    super.initState();
    _init();
  }

  Future<void> _init() async {
    try {
      // Esperamos un poco más para que la red esté 100% activa
      await Future.delayed(const Duration(seconds: 2));
      
      if (!mounted) return;
      
      // 1. Chequeo de actualizaciones (silencioso)
      final dio = DioClient(SecureStorageService());
      await AppUpdateService(dio).checkForUpdates(context);
      
      if (!mounted) return;

      // 2. Auth
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
      // Si falla cualquier cosa, vamos al login para no quedar bloqueados
      if (mounted) context.go('/login');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      body: Center(
        child: Image.asset(
          'assets/icons/logo.png',
          height: 150,
        ),
      ),
    );
  }
}


