import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:image_picker/image_picker.dart';
import '../providers/profile_provider.dart';
import '../../data/models/profile_model.dart';
import '../../../../core/theme/app_theme.dart';

class ProfileEditScreen extends ConsumerStatefulWidget {
  final ProfileModel profile;
  const ProfileEditScreen({super.key, required this.profile});

  @override
  ConsumerState<ProfileEditScreen> createState() => _ProfileEditScreenState();
}

class _ProfileEditScreenState extends ConsumerState<ProfileEditScreen> {
  late TextEditingController _nameController;
  late TextEditingController _pinController;
  late String _currentAvatar;
  late bool _isKid;
  File? _localImage;
  bool _uploading = false;

  // Avatares predefinidos (emojis/colores como generados)
  static const List<String> _presetAvatars = [
    'https://api.dicebear.com/8.x/avataaars/png?seed=Felix&backgroundColor=b6e3f4',
    'https://api.dicebear.com/8.x/avataaars/png?seed=Aneka&backgroundColor=ffdfbf',
    'https://api.dicebear.com/8.x/avataaars/png?seed=Mia&backgroundColor=d1d4f9',
    'https://api.dicebear.com/8.x/avataaars/png?seed=Leo&backgroundColor=c0aede',
    'https://api.dicebear.com/8.x/avataaars/png?seed=Max&backgroundColor=ffd5dc',
    'https://api.dicebear.com/8.x/avataaars/png?seed=Zoe&backgroundColor=b6e3f4',
  ];

  @override
  void initState() {
    super.initState();
    _nameController = TextEditingController(text: widget.profile.name);
    _pinController = TextEditingController();
    _currentAvatar = widget.profile.avatar ?? '';
    _isKid = widget.profile.isChild;
  }

  Future<void> _pickFromGallery() async {
    final picker = ImagePicker();
    final picked = await picker.pickImage(
      source: ImageSource.gallery,
      imageQuality: 80,
      maxWidth: 400,
    );
    if (picked != null) {
      setState(() => _localImage = File(picked.path));
      await _uploadAvatar(File(picked.path));
    }
  }

  Future<void> _uploadAvatar(File file) async {
    setState(() => _uploading = true);
    try {
      final url = await ref
          .read(profileProvider.notifier)
          .uploadAvatar(widget.profile.id, file);
      if (url != null) setState(() => _currentAvatar = url);
    } catch (_) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('No se pudo subir la imagen. Inténtalo de nuevo.'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } finally {
      if (mounted) setState(() => _uploading = false);
    }
  }

  void _showAvatarOptions() {
    showModalBottomSheet(
      context: context,
      backgroundColor: const Color(0xFF1a1a1e),
      shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (_) => Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const SizedBox(height: 12),
          Container(
              width: 40,
              height: 4,
              decoration: BoxDecoration(
                  color: Colors.white24,
                  borderRadius: BorderRadius.circular(2))),
          const SizedBox(height: 16),
          const Text('Cambiar foto de perfil',
              style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
          const Divider(color: Colors.white12),
          ListTile(
            leading: const Icon(Icons.photo_library_outlined, color: Colors.white),
            title: const Text('Elegir desde Galería', style: TextStyle(color: Colors.white)),
            onTap: () {
              Navigator.pop(context);
              _pickFromGallery();
            },
          ),
          ListTile(
            leading: const Icon(Icons.face_outlined, color: Colors.white),
            title: const Text('Avatares prediseñados', style: TextStyle(color: Colors.white)),
            onTap: () {
              Navigator.pop(context);
              _showPresetAvatars();
            },
          ),
          const SizedBox(height: 20),
        ],
      ),
    );
  }

  void _showPresetAvatars() {
    showModalBottomSheet(
      context: context,
      backgroundColor: const Color(0xFF1a1a1e),
      shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (_) => Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const SizedBox(height: 16),
          const Text('Elige tu avatar',
              style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
          const SizedBox(height: 16),
          Padding(
            padding: const EdgeInsets.all(16),
            child: Wrap(
              spacing: 12,
              runSpacing: 12,
              alignment: WrapAlignment.center,
              children: _presetAvatars.map((url) {
                final isSelected = _currentAvatar == url;
                return GestureDetector(
                  onTap: () {
                    setState(() {
                      _currentAvatar = url;
                      _localImage = null;
                    });
                    Navigator.pop(context);
                  },
                  child: Container(
                    decoration: BoxDecoration(
                      border: Border.all(
                          color: isSelected ? AppTheme.primaryRed : Colors.transparent,
                          width: 3),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(6),
                      child: Image.network(url, width: 80, height: 80, fit: BoxFit.cover),
                    ),
                  ),
                );
              }).toList(),
            ),
          ),
          const SizedBox(height: 20),
        ],
      ),
    );
  }

  Future<void> _saveChanges() async {
    final name = _nameController.text.trim();
    if (name.isEmpty) return;

    await ref.read(profileProvider.notifier).updateProfile(widget.profile.id, {
      'name': name,
      'avatar': _currentAvatar,
      'is_kid': _isKid,
      'pin': _pinController.text.length == 4 ? _pinController.text : null,
    });

    if (mounted) Navigator.pop(context);
  }

  Future<void> _deleteProfile() async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        backgroundColor: Colors.grey[900],
        title: const Text('¿Eliminar perfil?', style: TextStyle(color: Colors.white)),
        content: const Text('Esta acción no se puede deshacer.', style: TextStyle(color: Colors.grey)),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Cancelar')),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Eliminar', style: TextStyle(color: AppTheme.primaryRed)),
          ),
        ],
      ),
    );
    if (confirmed == true) {
      await ref.read(profileProvider.notifier).deleteProfile(widget.profile.id);
      if (mounted) Navigator.pop(context);
    }
  }

  @override
  Widget build(BuildContext context) {
    ImageProvider avatarImage;
    if (_localImage != null) {
      avatarImage = FileImage(_localImage!);
    } else if (_currentAvatar.isNotEmpty) {
      avatarImage = NetworkImage(_currentAvatar);
    } else {
      avatarImage = const AssetImage('assets/images/placeholder.png');
    }

    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        backgroundColor: Colors.black,
        title: const Text('Editar Perfil'),
        actions: [
          TextButton(
            onPressed: _saveChanges,
            child: const Text('Guardar',
                style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Column(
          children: [
            // Avatar con botón de cámara
            GestureDetector(
              onTap: _showAvatarOptions,
              child: Stack(
                alignment: Alignment.bottomRight,
                children: [
                  CircleAvatar(
                    radius: 60,
                    backgroundImage: avatarImage,
                    backgroundColor: Colors.grey[900],
                  ),
                  if (_uploading)
                    Positioned.fill(
                      child: Container(
                        decoration: const BoxDecoration(
                            color: Colors.black54, shape: BoxShape.circle),
                        child: const Center(
                            child: CircularProgressIndicator(
                                color: AppTheme.primaryRed, strokeWidth: 2)),
                      ),
                    ),
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: AppTheme.primaryRed,
                      shape: BoxShape.circle,
                      border: Border.all(color: Colors.black, width: 2),
                    ),
                    child: const Icon(Icons.camera_alt, color: Colors.white, size: 16),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 8),
            TextButton(
              onPressed: _showAvatarOptions,
              child: const Text('Cambiar foto de perfil',
                  style: TextStyle(color: AppTheme.primaryRed)),
            ),
            const SizedBox(height: 24),

            // Nombre
            TextField(
              controller: _nameController,
              style: const TextStyle(color: Colors.white),
              decoration: InputDecoration(
                labelText: 'Nombre del perfil',
                labelStyle: const TextStyle(color: Colors.grey),
                filled: true,
                fillColor: Colors.grey[900],
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
              ),
            ),
            const SizedBox(height: 20),

            // Modo niños
            SwitchListTile(
              tileColor: Colors.grey[900],
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
              title: const Text('Modo Niños', style: TextStyle(color: Colors.white)),
              subtitle: const Text('Solo contenido apto para todas las edades',
                  style: TextStyle(color: Colors.grey)),
              value: _isKid,
              onChanged: (v) => setState(() => _isKid = v),
              activeColor: AppTheme.primaryRed,
            ),
            const SizedBox(height: 20),

            // PIN
            TextField(
              controller: _pinController,
              style: const TextStyle(color: Colors.white),
              keyboardType: TextInputType.number,
              maxLength: 4,
              obscureText: true,
              decoration: InputDecoration(
                labelText: 'PIN de Seguridad (4 dígitos)',
                hintText: 'Déjalo vacío para no usar PIN',
                hintStyle: const TextStyle(color: Colors.white24, fontSize: 12),
                labelStyle: const TextStyle(color: Colors.grey),
                filled: true,
                fillColor: Colors.grey[900],
                prefixIcon: const Icon(Icons.lock_outline, color: Colors.grey),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
              ),
            ),
            const SizedBox(height: 40),

            // Eliminar
            TextButton.icon(
              onPressed: _deleteProfile,
              icon: const Icon(Icons.delete_outline, color: Colors.grey),
              label: const Text('Eliminar Perfil', style: TextStyle(color: Colors.grey)),
            ),
          ],
        ),
      ),
    );
  }
}
