import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../providers/profile_provider.dart';
import '../../../../core/theme/app_theme.dart';

class PinScreen extends ConsumerStatefulWidget {
  const PinScreen({super.key});

  @override
  ConsumerState<PinScreen> createState() => _PinScreenState();
}

class _PinScreenState extends ConsumerState<PinScreen> {
  final List<String> _pin = [];
  final int _pinLength = 4;

  void _onKeyTap(String key) async {
    if (_pin.length < _pinLength) {
      setState(() => _pin.add(key));
    }

    if (_pin.length == _pinLength) {
      final success = await ref.read(profileProvider.notifier).verifyPin(_pin.join());
      if (success && mounted) {
        context.go('/home');
      } else {
        setState(() => _pin.clear());
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('PIN incorrecto'), backgroundColor: Colors.red),
          );
        }
      }
    }
  }

  void _onDelete() {
    if (_pin.isNotEmpty) {
      setState(() => _pin.removeLast());
    }
  }

  @override
  Widget build(BuildContext context) {
    final profile = ref.watch(profileProvider).selectedProfile;

    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => context.pop(),
        ),
      ),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Text(
              'Ingresa tu PIN para desbloquear este perfil',
              style: TextStyle(color: Colors.white, fontSize: 18),
            ),
            const SizedBox(height: 32),
            
            // Indicadores de PIN
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: List.generate(_pinLength, (index) {
                return Container(
                  width: 20,
                  height: 20,
                  margin: const EdgeInsets.symmetric(horizontal: 10),
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: index < _pin.length ? Colors.white : Colors.grey[800],
                  ),
                );
              }),
            ),
            
            const SizedBox(height: 48),
            
            // Teclado Numérico
            SizedBox(
              width: 250,
              child: GridView.count(
                shrinkWrap: true,
                crossAxisCount: 3,
                mainAxisSpacing: 10,
                crossAxisSpacing: 10,
                children: [
                  ...List.generate(9, (index) => _PinButton(label: '${index + 1}', onTap: () => _onKeyTap('${index + 1}'))),
                  const SizedBox(),
                  _PinButton(label: '0', onTap: () => _onKeyTap('0')),
                  _PinButton(label: '⌫', onTap: _onDelete),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _PinButton extends StatelessWidget {
  final String label;
  final VoidCallback onTap;

  const _PinButton({required this.label, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      child: Container(
        alignment: Alignment.center,
        decoration: BoxDecoration(
          border: Border.all(color: Colors.white24),
          shape: BoxShape.circle,
        ),
        child: Text(
          label,
          style: const TextStyle(color: Colors.white, fontSize: 24, fontWeight: FontWeight.bold),
        ),
      ),
    );
  }
}
