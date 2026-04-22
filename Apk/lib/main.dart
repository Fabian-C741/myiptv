import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:media_kit/media_kit.dart';
import 'app.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  // Inicialización necesaria para el reproductor de video
  MediaKit.ensureInitialized();

  runApp(
    const ProviderScope(
      child: OttApp(),
    ),
  );
}
