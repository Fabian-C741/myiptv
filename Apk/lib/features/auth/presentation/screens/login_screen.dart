import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../providers/auth_provider.dart';
import '../../../../core/theme/app_theme.dart';
import '../../../../core/network/dio_client.dart';
import '../../../../core/storage/secure_storage_service.dart';
import 'package:url_launcher/url_launcher.dart';

class LoginScreen extends ConsumerStatefulWidget {
  const LoginScreen({super.key});

  @override
  ConsumerState<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends ConsumerState<LoginScreen> {
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _obscurePassword = true;

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  void _handleLogin() async {
    final email = _emailController.text.trim();
    final password = _passwordController.text.trim();

    if (email.isEmpty || password.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Por favor completa todos los campos')),
      );
      return;
    }

    await ref.read(authProvider.notifier).login(email, password);
  }

  void _contactSupport() async {
    try {
      final dio = DioClient(SecureStorageService()).instance;
      final response = await dio.get('/app/config');
      final waNumber = response.data['whatsapp_contact'] ?? '5491100000000';
      final cleanNumber = waNumber.toString().replaceAll(RegExp(r'[^0-9]'), '');
      final whatsappUrl = Uri.parse("https://wa.me/$cleanNumber?text=Hola,%20necesito%20ayuda%20con%20ElectroFabi%20IPTV");
      if (await canLaunchUrl(whatsappUrl)) {
        await launchUrl(whatsappUrl, mode: LaunchMode.externalApplication);
      }
    } catch (e) {
      final fallbackUrl = Uri.parse("https://wa.me/5491100000000?text=Hola,%20necesito%20ayuda");
      if (await canLaunchUrl(fallbackUrl)) {
        await launchUrl(fallbackUrl, mode: LaunchMode.externalApplication);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authProvider);

    // Escuchar cambios para navegar si el login es exitoso
    ref.listen(authProvider, (previous, next) {
      if (next.isAuthenticated) {
        context.go('/profiles');
      }
      if (next.error != null) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(next.error!), backgroundColor: Colors.red),
        );
      }
    });

    return Scaffold(
      body: Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [Colors.black.withOpacity(0.8), Colors.black],
          ),
        ),
        child: Center(
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 400),
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(32),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Image.asset('assets/icons/logo.png', height: 100),
                  const SizedBox(height: 24),
                  const Text(
                    'ELECTROFABI IPTV',
                    style: TextStyle(
                      fontSize: 28,
                      fontWeight: FontWeight.w900,
                      color: AppTheme.primaryRed,
                      letterSpacing: 1.5,
                    ),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 48),
                  
                  // Campo Email
                  TextField(
                    controller: _emailController,
                    keyboardType: TextInputType.emailAddress,
                    style: const TextStyle(color: Colors.white),
                    decoration: InputDecoration(
                      labelText: 'Usuario / Email',
                      labelStyle: const TextStyle(color: Colors.white70),
                      filled: true,
                      fillColor: Colors.white12,
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
                    ),
                  ),
                  const SizedBox(height: 16),
                  
                  // Campo Password
                  TextField(
                    controller: _passwordController,
                    obscureText: _obscurePassword,
                    style: const TextStyle(color: Colors.white),
                    decoration: InputDecoration(
                      labelText: 'Contraseña',
                      labelStyle: const TextStyle(color: Colors.white70),
                      filled: true,
                      fillColor: Colors.white12,
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
                      suffixIcon: IconButton(
                        icon: Icon(_obscurePassword ? Icons.visibility : Icons.visibility_off, color: Colors.white54),
                        onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
                      ),
                    ),
                  ),
                  const SizedBox(height: 32),
                  
                  // Botón Login
                  ElevatedButton(
                    onPressed: authState.isLoading ? null : _handleLogin,
                    style: ElevatedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      backgroundColor: AppTheme.primaryRed,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                    child: authState.isLoading
                        ? const SizedBox(
                            height: 20,
                            width: 20,
                            child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                          )
                        : const Text('ENTRAR', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                  ),
                  const SizedBox(height: 24),
                  
                  TextButton(
                    onPressed: _contactSupport,
                    child: const Text('¿Necesitas ayuda?', style: TextStyle(color: Colors.white70)),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
