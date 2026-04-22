import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../../features/auth/presentation/screens/splash_screen.dart';
import '../../features/auth/presentation/screens/login_screen.dart';
import '../../features/profiles/presentation/screens/profiles_screen.dart';
import '../../features/profiles/presentation/screens/pin_screen.dart';
import '../../features/home/presentation/screens/home_screen.dart';
import '../../features/live_tv/presentation/screens/live_tv_screen.dart';
import '../../features/player/presentation/screens/player_screen.dart';
import '../../features/settings/presentation/screens/settings_screen.dart';
import '../../features/settings/presentation/screens/devices_screen.dart';
import '../../features/home/presentation/screens/details_screen.dart';
import '../../shared/models/channel_model.dart';

class AppRouter {
  static final router = GoRouter(
    initialLocation: '/splash',
    routes: [
      GoRoute(
        path: '/splash',
        builder: (context, state) => const SplashScreen(),
      ),
      GoRoute(
        path: '/login',
        builder: (context, state) => const LoginScreen(),
      ),
      GoRoute(
        path: '/profiles',
        builder: (context, state) => const ProfilesScreen(),
      ),
      GoRoute(
        path: '/pin',
        builder: (context, state) => const PinScreen(),
      ),
      GoRoute(
        path: '/home',
        builder: (context, state) => const HomeScreen(),
      ),
      GoRoute(
        path: '/live-tv',
        builder: (context, state) => const LiveTvScreen(),
      ),
      GoRoute(
        path: '/player',
        builder: (context, state) {
          final channel = state.extra as ChannelModel;
          return PlayerScreen(channel: channel);
        },
      ),
      GoRoute(
        path: '/details',
        builder: (context, state) {
          final channel = state.extra as ChannelModel;
          return DetailsScreen(content: channel);
        },
      ),
      GoRoute(
        path: '/settings',
        builder: (context, state) => const SettingsScreen(),
      ),
      GoRoute(
        path: '/devices',
        builder: (context, state) => const DevicesScreen(),
      ),
    ],
  );
}
