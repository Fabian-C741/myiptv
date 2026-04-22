import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'core/theme/app_theme.dart';
import 'core/router/app_router.dart';

import 'package:ott_app/core/services/update_service.dart';
import 'package:ott_app/core/network/dio_client.dart';
import 'package:ott_app/core/storage/secure_storage_service.dart';

class OttApp extends ConsumerStatefulWidget {
  const OttApp({super.key});

  @override
  ConsumerState<OttApp> createState() => _OttAppState();
}

class _OttAppState extends ConsumerState<OttApp> {
  @override
  void initState() {
    super.initState();
    // Iniciar verificación de actualización tras el primer frame
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final dio = DioClient(SecureStorageService());
      AppUpdateService(dio).checkForUpdates(context);
    });
  }

  @override
  Widget build(BuildContext context) {
    return MaterialApp.router(
      title: 'Electrofabiptv',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.darkTheme,
      routerConfig: AppRouter.router,
    );
  }
}
