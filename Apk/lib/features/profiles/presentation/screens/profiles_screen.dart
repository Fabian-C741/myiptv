import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../providers/profile_provider.dart';
import '../../../../core/theme/app_theme.dart';
import '../../../../core/config/brand_provider.dart'; // Import nuevo
import 'profile_edit_screen.dart';

class ProfilesScreen extends ConsumerStatefulWidget {
  const ProfilesScreen({super.key});

  @override
  ConsumerState<ProfilesScreen> createState() => _ProfilesScreenState();
}

class _ProfilesScreenState extends ConsumerState<ProfilesScreen> {
  bool _isEditing = false;

  @override
  Widget build(BuildContext context) {
    final profileState = ref.watch(profileProvider);
    final brandState = ref.watch(brandProvider);
    final appConfig = brandState.config;

    return Scaffold(
      backgroundColor: const Color(0xFF000000),
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        centerTitle: true,
        title: appConfig?.logoUrl != null
          ? Image.network(appConfig!.logoUrl!, height: 40, errorBuilder: (c, e, s) => _buildBrandText(appConfig.appName))
          : _buildBrandText(appConfig?.appName ?? 'ELECTROFABI'),
      ),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Text(
              '¿Quién está viendo?',
              style: TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.w400,
                color: Colors.white,
                letterSpacing: 0.5,
              ),
            ),
            const SizedBox(height: 40),
            if (profileState.isLoading)
              const CircularProgressIndicator(color: AppTheme.primaryRed)
            else if (profileState.error != null)
              Text(profileState.error!, style: const TextStyle(color: Colors.red))
            else
              _ProfilesGrid(
                profiles: profileState.profiles, 
                isEditing: _isEditing,
              ),
            const SizedBox(height: 60),
            // Botón Administrar Perfiles Estilo Netflix
            OutlinedButton(
              onPressed: () => setState(() => _isEditing = !_isEditing),
              style: OutlinedButton.styleFrom(
                side: const BorderSide(color: Colors.white38),
                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 8),
              ),
              child: Text(
                _isEditing ? 'LISTO' : 'ADMINISTRAR PERFILES',
                style: const TextStyle(color: Colors.white70, fontSize: 14, letterSpacing: 1.5),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildBrandText(String name) {
    return Text(
      name,
      style: const TextStyle(
        color: AppTheme.primaryRed,
        fontWeight: FontWeight.w900,
        fontSize: 22,
        letterSpacing: 2,
      ),
    );
  }
}

class _ProfilesGrid extends ConsumerWidget {
  final List<dynamic> profiles;
  final bool isEditing;
  const _ProfilesGrid({required this.profiles, required this.isEditing});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final canAdd = profiles.length < 5;
    
    return Wrap(
      spacing: 24,
      runSpacing: 24,
      alignment: WrapAlignment.center,
      children: [
        ...profiles.map((profile) => _ProfileCard(profile: profile, isEditing: isEditing)),
        if (canAdd)
          GestureDetector(
            onTap: () => _showAddProfileDialog(context, ref),
            child: Column(
              children: [
                Container(
                  width: 110,
                  height: 110,
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(4),
                    border: Border.all(color: Colors.white38, width: 2),
                  ),
                  child: const Icon(Icons.add, color: Colors.white38, size: 40),
                ),
                const SizedBox(height: 12),
                const Text('Agregar', style: TextStyle(color: Color(0xFFE5E5E5), fontSize: 14)),
              ],
            ),
          ),
      ],
    );
  }

  void _showAddProfileDialog(BuildContext context, WidgetRef ref) {
    final nameController = TextEditingController();
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: const Color(0xFF1A1A1E),
        title: const Text('Nuevo Perfil', style: TextStyle(color: Colors.white)),
        content: TextField(
          controller: nameController,
          style: const TextStyle(color: Colors.white),
          decoration: const InputDecoration(
            hintText: 'Nombre del perfil',
            hintStyle: TextStyle(color: Colors.white38),
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('CANCELAR', style: TextStyle(color: Colors.white54)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: AppTheme.primaryRed),
            onPressed: () {
              if (nameController.text.isNotEmpty) {
                ref.read(profileProvider.notifier).addProfile(nameController.text);
                Navigator.pop(ctx);
              }
            },
            child: const Text('AGREGAR'),
          ),
        ],
      ),
    );
  }
}

class _ProfileCard extends ConsumerWidget {
  final dynamic profile;
  final bool isEditing;
  const _ProfileCard({super.key, required this.profile, required this.isEditing});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return GestureDetector(
      onTap: () {
        if (isEditing) {
          Navigator.push(
            context, 
            MaterialPageRoute(builder: (_) => ProfileEditScreen(profile: profile))
          );
        } else {
          ref.read(profileProvider.notifier).selectProfile(profile);
          if (profile.hasPin) {
            context.push('/pin');
          } else {
            context.go('/home');
          }
        }
      },
      child: Column(
        children: [
          Stack(
            alignment: Alignment.center,
            children: [
              Container(
                width: 110,
                height: 110,
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(4), // Netflix usa bordes muy poco redondeados
                  image: DecorationImage(
                    image: profile.avatar != null && profile.avatar!.isNotEmpty
                      ? NetworkImage(profile.avatar!) 
                      : const AssetImage('assets/images/placeholder.png') as ImageProvider,
                    fit: BoxFit.cover,
                  ),
                ),
              ),
              if (isEditing)
                Container(
                  width: 110,
                  height: 110,
                  decoration: BoxDecoration(
                    color: Colors.black54,
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: const Icon(Icons.edit_outlined, color: Colors.white, size: 40),
                ),
            ],
          ),
          const SizedBox(height: 12),
          Text(
            profile.name,
            style: const TextStyle(color: Color(0xFFE5E5E5), fontSize: 14),
          ),
        ],
      ),
    );
  }
}
