import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:flutter_animate/flutter_animate.dart';
import '../providers/auth_provider.dart';
import '../../../../core/providers/brand_provider.dart';
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
    _initApp();
  }

  Future<void> _initApp() async {
    try {
        // 1. Verificar Actualizaciones (Con tiempo límite)
        final dio = DioClient(SecureStorageService());
        await AppUpdateService(dio).checkForUpdates(context);
    } catch (e) {
        debugPrint('Error en el inicio: $e');
    }

    if (!mounted) return;

    // 2. Esperar un poco para la animación
    await Future.delayed(const Duration(seconds: 1));
    
    try {
        // 3. Verificar Auth
        await ref.read(authProvider.notifier).checkAuthStatus();
    } catch (e) {
        debugPrint('Error en auth check: $e');
    }
    
    if (mounted) {
      final authState = ref.read(authProvider);
      if (authState.isAuthenticated) {
        context.go('/profiles');
      } else {
        context.go('/login');
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final brand = ref.watch(brandProvider);
    
    return Scaffold(
      backgroundColor: Colors.black,
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            // Logo Dinámico
            if (brand.logoUrl != null)
                Image.network(
                    brand.logoUrl!,
                    height: 120,
                    errorBuilder: (_, __, ___) => const Icon(Icons.play_circle_fill, size: 100, color: AppTheme.primaryRed),
                ).animate().fadeIn(duration: 800.ms).scale(begin: const Offset(0.8, 0.8))
            else
                const Icon(
                  Icons.play_circle_fill,
                  size: 100,
                  color: AppTheme.primaryRed,
                ).animate().fadeIn(duration: 800.ms).scale(begin: const Offset(0.5, 0.5)),
            
            const SizedBox(height: 24),
            Text(
                brand.name,
                style: const TextStyle(color: Colors.white, fontSize: 24, fontWeight: FontWeight.bold, letterSpacing: 1.2)
            ).animate().fadeIn(delay: 400.ms),
            
            const SizedBox(height: 48),
            const CircularProgressIndicator(
              valueColor: AlwaysStoppedAnimation<Color>(AppTheme.primaryRed),
              strokeWidth: 2,
            ),
          ],
        ),
      ),
    );
  }
}


