import 'package:flutter/material.dart';
import '../../../../core/theme/app_theme.dart';

class AvatarSelectionScreen extends StatelessWidget {
  const AvatarSelectionScreen({super.key});

  static const List<Map<String, String>> availableAvatars = [
    {'url': 'https://img.freepik.com/vector-premium/avatar-perfil-dibujos-animados-personaje-hombre-joven_113065-274.jpg', 'label': 'Heroe'},
    {'url': 'https://img.freepik.com/vector-premium/avatar-perfil-dibujos-animados-personaje-mujer-joven_113065-276.jpg', 'label': 'Heroina'},
    {'url': 'https://img.freepik.com/vector-premium/avatar-perfil-dibujos-animados-personaje-hombre-barba_113065-280.jpg', 'label': 'Sabio'},
    {'url': 'https://img.freepik.com/vector-premium/avatar-perfil-dibujos-animados-personaje-nina_113065-285.jpg', 'label': 'Niña'},
    {'url': 'https://img.freepik.com/vector-premium/personaje-dibujos-animados-monstruo-divertido_113065-156.jpg', 'label': 'Monstruo'},
    {'url': 'https://img.freepik.com/vector-premium/perro-dibujos-animados-gracioso_113065-300.jpg', 'label': 'Mascota'},
    {'url': 'https://img.freepik.com/vector-premium/personaje-robot-futurista_113065-420.jpg', 'label': 'Robot'},
    {'url': 'https://img.freepik.com/vector-premium/avatar-nino-aventurero_113065-290.jpg', 'label': 'Explorador'},
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        title: const Text('Elige un Personaje', style: TextStyle(fontWeight: FontWeight.bold)),
        backgroundColor: Colors.transparent,
        elevation: 0,
      ),
      body: GridView.builder(
        padding: const EdgeInsets.all(24),
        gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: 3,
          mainAxisSpacing: 20,
          crossAxisSpacing: 20,
          childAspectRatio: 1,
        ),
        itemCount: availableAvatars.length,
        itemBuilder: (context, index) {
          final avatar = availableAvatars[index];
          return InkWell(
            onTap: () => Navigator.pop(context, avatar['url']),
            child: Container(
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(12),
                image: DecorationImage(
                  image: NetworkImage(avatar['url']!),
                  fit: BoxFit.cover,
                ),
                border: Border.all(color: Colors.transparent, width: 3),
              ),
              child: Stack(
                children: [
                  Positioned(
                    bottom: 0,
                    left: 0,
                    right: 0,
                    child: Container(
                      padding: const EdgeInsets.symmetric(vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.black.withOpacity(0.6),
                        borderRadius: const BorderRadius.vertical(bottom: Radius.circular(10)),
                      ),
                      child: Text(
                        avatar['label']!,
                        textAlign: TextAlign.center,
                        style: const TextStyle(color: Colors.white, fontSize: 10),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }
}
