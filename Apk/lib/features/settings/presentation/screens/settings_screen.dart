import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../providers/settings_provider.dart';
import 'package:ott_app/features/auth/presentation/providers/auth_provider.dart';
import 'package:ott_app/core/theme/app_theme.dart';

class SettingsScreen extends ConsumerWidget {
  const SettingsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(title: const Text('Configuración')),
      body: ListView(
        children: [
          _SettingTile(
            title: 'Dispositivos Conectados',
            subtitle: 'Gestiona tus sesiones activas',
            icon: Icons.devices,
            onTap: () => context.push('/devices'),
          ),
          if (ref.watch(authProvider).user?.role == 'superadmin')
            _SettingTile(
              title: 'Panel Control Admin',
              subtitle: 'Gestionar canales y permisos',
              icon: Icons.admin_panel_settings,
              color: Colors.amber,
              onTap: () {
                // Abrir panel web o sección admin
              },
            ),
          _SettingTile(
            title: 'Cambiar Mi PIN',
            subtitle: 'Protege tu perfil con un código de 4 dígitos',
            icon: Icons.lock_outline,
            onTap: () {
              // Diálogo para cambiar PIN
            },
          ),
          _SettingTile(
            title: 'Cambiar Avatar',
            subtitle: 'Personaliza tu imagen de perfil',
            icon: Icons.face,
            onTap: () {},
          ),
          const Divider(color: Colors.white24),
          _SettingTile(
            title: 'Cerrar Sesión',
            subtitle: 'Salir de esta cuenta en este dispositivo',
            icon: Icons.logout,
            color: AppTheme.primaryRed,
            onTap: () {
              ref.read(authProvider.notifier).logout();
              context.go('/login');
            },
          ),
        ],
      ),
    );
  }
}

class _SettingTile extends StatelessWidget {
  final String title;
  final String subtitle;
  final IconData icon;
  final VoidCallback onTap;
  final Color? color;

  const _SettingTile({
    required this.title,
    required this.subtitle,
    required this.icon,
    required this.onTap,
    this.color,
  });

  @override
  Widget build(BuildContext context) {
    return ListTile(
      leading: Icon(icon, color: color ?? Colors.white, size: 28),
      title: Text(title, style: TextStyle(color: color ?? Colors.white, fontWeight: FontWeight.bold)),
      subtitle: Text(subtitle, style: const TextStyle(color: Colors.grey)),
      trailing: const Icon(Icons.chevron_right, color: Colors.grey),
      onTap: onTap,
    );
  }
}
