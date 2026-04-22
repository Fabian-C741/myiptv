import 'package:flutter/material.dart';

class AppTheme {
  // Colores principales (Electrofabiptv)
  static const Color primaryBlue = Color(0xFF00AAFF);
  static const Color primaryRed = Color(0xFFFF3333);
  static const Color backgroundBlack = Color(0xFF000000);
  static const Color surfaceGrey = Color(0xFF121212);
  static const Color textWhite = Color(0xFFFFFFFF);
  static const Color textGrey = Color(0xFFAAAAAA);

  static ThemeData get darkTheme {
    return ThemeData(
      useMaterial3: true,
      brightness: Brightness.dark,
      primaryColor: primaryBlue,
      scaffoldBackgroundColor: backgroundBlack,
      colorScheme: const ColorScheme.dark(
        primary: primaryBlue,
        secondary: primaryRed,
        surface: surfaceGrey,
        background: backgroundBlack,
        onPrimary: textWhite,
        onSurface: textWhite,
      ),
      appBarTheme: const AppBarTheme(
        backgroundColor: Colors.transparent,
        elevation: 0,
        centerTitle: true,
      ),
      textTheme: const TextTheme(
        displayLarge: TextStyle(color: textWhite, fontWeight: FontWeight.bold, fontSize: 32),
        displayMedium: TextStyle(color: textWhite, fontWeight: FontWeight.bold, fontSize: 24),
        bodyLarge: TextStyle(color: textWhite, fontSize: 16),
        bodyMedium: TextStyle(color: textGrey, fontSize: 14),
      ),
      // Estilo para botones de Android TV (Focus)
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: primaryBlue,
          foregroundColor: textWhite,
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(4)),
        ),
      ),
    );
  }
}
