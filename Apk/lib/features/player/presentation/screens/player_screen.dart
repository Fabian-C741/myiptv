import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:media_kit/media_kit.dart' hide PlayerState;
import 'package:media_kit_video/media_kit_video.dart';
import '../providers/player_provider.dart';
import 'package:ott_app/shared/models/channel_model.dart';
import '../../../../core/theme/app_theme.dart';

class PlayerScreen extends ConsumerStatefulWidget {
  final ChannelModel channel;
  const PlayerScreen({super.key, required this.channel});

  @override
  ConsumerState<PlayerScreen> createState() => _PlayerScreenState();
}

class _PlayerScreenState extends ConsumerState<PlayerScreen> {
  late final VideoController _videoController;
  bool _showControls = true;
  DateTime? _lastActivity;

  @override
  void initState() {
    super.initState();
    final player = ref.read(playerProvider('global')).player;
    _videoController = VideoController(player);
    Future.microtask(() =>
        ref.read(playerProvider('global').notifier).playChannel(widget.channel));
    _resetTimer();
  }

  void _resetTimer() {
    _lastActivity = DateTime.now();
    if (!_showControls) setState(() => _showControls = true);
    Future.delayed(const Duration(seconds: 5), () {
      if (mounted &&
          _lastActivity != null &&
          DateTime.now().difference(_lastActivity!) >= const Duration(seconds: 5)) {
        setState(() => _showControls = false);
      }
    });
  }

  @override
  void dispose() {
    ref.read(playerProvider('global').notifier).stop();
    super.dispose();
  }

  void _showAudioPicker(BuildContext context, PlayerState playerState) {
    final tracks = playerState.tracks.audio;
    final active = playerState.activeAudioTrack;

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
          const Text('Pistas de Audio',
              style: TextStyle(
                  color: Colors.white,
                  fontSize: 18,
                  fontWeight: FontWeight.bold)),
          const Divider(color: Colors.white12),
          if (tracks.isEmpty)
            const Padding(
              padding: EdgeInsets.all(24),
              child: Text('No hay pistas adicionales disponibles',
                  style: TextStyle(color: Colors.grey)),
            )
          else
            ListView.builder(
              shrinkWrap: true,
              itemCount: tracks.length,
              itemBuilder: (_, i) {
                final track = tracks[i];
                final isActive = active?.id == track.id;
                final label = track.title?.isNotEmpty == true
                    ? track.title!
                    : track.language?.isNotEmpty == true
                        ? 'Audio — ${track.language!.toUpperCase()}'
                        : 'Pista ${i + 1}';
                return ListTile(
                  leading: Icon(
                    isActive ? Icons.volume_up : Icons.audiotrack,
                    color: isActive ? AppTheme.primaryRed : Colors.white54,
                  ),
                  title: Text(label,
                      style: TextStyle(
                          color: isActive ? AppTheme.primaryRed : Colors.white,
                          fontWeight: isActive
                              ? FontWeight.bold
                              : FontWeight.normal)),
                  trailing: isActive
                      ? const Icon(Icons.check_circle,
                          color: AppTheme.primaryRed)
                      : null,
                  onTap: () {
                    ref
                        .read(playerProvider('global').notifier)
                        .setAudioTrack(track);
                    Navigator.pop(context);
                  },
                );
              },
            ),
          const SizedBox(height: 20),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final playerState = ref.watch(playerProvider('global'));

    return Scaffold(
      backgroundColor: Colors.black,
      body: GestureDetector(
        onTap: _resetTimer,
        child: Stack(
          children: [
            // Video
            Center(
              child: Video(controller: _videoController, fill: Colors.black),
            ),

            // Buffering
            if (playerState.isBuffering && !playerState.isPlaying)
              Center(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const CircularProgressIndicator(
                        color: AppTheme.primaryRed, strokeWidth: 3),
                    const SizedBox(height: 12),
                    Text(
                      playerState.retryCount > 0
                          ? 'Reintentando (${playerState.retryCount}/3)...'
                          : 'Cargando...',
                      style: const TextStyle(color: Colors.white70),
                    ),
                  ],
                ),
              ),

            // Error
            if (playerState.error != null)
              Center(
                child: Container(
                  margin: const EdgeInsets.all(32),
                  padding: const EdgeInsets.all(24),
                  decoration: BoxDecoration(
                    color: Colors.black87,
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(Icons.signal_wifi_bad,
                          color: AppTheme.primaryRed, size: 56),
                      const SizedBox(height: 16),
                      Text(playerState.error!,
                          textAlign: TextAlign.center,
                          style: const TextStyle(
                              color: Colors.white, fontSize: 14)),
                      const SizedBox(height: 24),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          OutlinedButton.icon(
                            onPressed: () => Navigator.pop(context),
                            icon: const Icon(Icons.arrow_back,
                                color: Colors.white54),
                            label: const Text('Volver',
                                style: TextStyle(color: Colors.white54)),
                            style: OutlinedButton.styleFrom(
                                side: const BorderSide(
                                    color: Colors.white24)),
                          ),
                          const SizedBox(width: 12),
                          ElevatedButton.icon(
                            onPressed: () => ref
                                .read(playerProvider('global').notifier)
                                .retry(),
                            icon: const Icon(Icons.refresh),
                            label: const Text('Reintentar'),
                            style: ElevatedButton.styleFrom(
                                backgroundColor: AppTheme.primaryRed),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),

            // Controles
            if (_showControls)
              _Controls(
                channel: widget.channel,
                isPlaying: playerState.isPlaying,
                activeTrack: playerState.activeAudioTrack,
                hasAudioTracks: playerState.tracks.audio.length > 1,
                onTogglePlay: () =>
                    ref.read(playerProvider('global').notifier).togglePlay(),
                onAudio: () =>
                    _showAudioPicker(context, playerState),
                onBack: () => Navigator.pop(context),
              ),
          ],
        ),
      ),
    );
  }
}

class _Controls extends StatelessWidget {
  final ChannelModel channel;
  final bool isPlaying;
  final AudioTrack? activeTrack;
  final bool hasAudioTracks;
  final VoidCallback onTogglePlay;
  final VoidCallback onAudio;
  final VoidCallback onBack;

  const _Controls({
    required this.channel,
    required this.isPlaying,
    required this.activeTrack,
    required this.hasAudioTracks,
    required this.onTogglePlay,
    required this.onAudio,
    required this.onBack,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.bottomCenter,
          end: Alignment.topCenter,
          colors: [
            Colors.black.withOpacity(0.85),
            Colors.transparent,
            Colors.black.withOpacity(0.5),
          ],
        ),
      ),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          // Top bar
          SafeArea(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Row(
                children: [
                  IconButton(
                    icon: const Icon(Icons.arrow_back,
                        color: Colors.white, size: 28),
                    onPressed: onBack,
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      channel.displayName,
                      style: const TextStyle(
                          color: Colors.white,
                          fontSize: 18,
                          fontWeight: FontWeight.bold),
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  // Botón audio con indicador del track activo
                  Stack(
                    alignment: Alignment.topRight,
                    children: [
                      IconButton(
                        icon: const Icon(Icons.audiotrack,
                            color: Colors.white, size: 26),
                        onPressed: onAudio,
                        tooltip: 'Cambiar Audio',
                      ),
                      if (activeTrack?.language != null)
                        Positioned(
                          top: 6,
                          right: 6,
                          child: Container(
                            padding: const EdgeInsets.all(3),
                            decoration: BoxDecoration(
                              color: AppTheme.primaryRed,
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Text(
                              activeTrack!.language!.toUpperCase().substring(0, 2.clamp(0, activeTrack!.language!.length)),
                              style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 8,
                                  fontWeight: FontWeight.bold),
                            ),
                          ),
                        ),
                    ],
                  ),
                ],
              ),
            ),
          ),

          // Play/Pause central
          Padding(
            padding: const EdgeInsets.only(bottom: 48),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                IconButton(
                  icon: Icon(
                    isPlaying
                        ? Icons.pause_circle_filled
                        : Icons.play_circle_filled,
                    color: Colors.white,
                    size: 72,
                  ),
                  onPressed: onTogglePlay,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
