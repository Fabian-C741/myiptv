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
    // No detenemos el player inmediatamente para permitir transiciones suaves
    // ref.read(playerProvider('global').notifier).stop();
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
                isMuted: playerState.player.state.volume == 0,
                position: playerState.player.state.position,
                duration: playerState.player.state.duration,
                activeTrack: playerState.activeAudioTrack,
                hasAudioTracks: playerState.tracks.audio.length > 1,
                onTogglePlay: () =>
                    ref.read(playerProvider('global').notifier).togglePlay(),
                onMute: () {
                    final currentVol = playerState.player.state.volume;
                    playerState.player.setVolume(currentVol > 0 ? 0 : 100);
                    setState(() {});
                },
                onSeek: (position) => playerState.player.seek(position),
                onAudio: () =>
                    _showAudioPicker(context, playerState),
                onBack: () {
                  // Si es VOD, pausamos al salir, si es TV podemos dejarlo
                  if (widget.channel.type != 'live') {
                      ref.read(playerProvider('global').notifier).stop();
                  }
                  Navigator.pop(context);
                },
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
  final bool isMuted;
  final Duration position;
  final Duration duration;
  final AudioTrack? activeTrack;
  final bool hasAudioTracks;
  final VoidCallback onTogglePlay;
  final VoidCallback onMute;
  final Function(Duration) onSeek;
  final VoidCallback onAudio;
  final VoidCallback onBack;

  const _Controls({
    required this.channel,
    required this.isPlaying,
    required this.isMuted,
    required this.position,
    required this.duration,
    required this.activeTrack,
    required this.hasAudioTracks,
    required this.onTogglePlay,
    required this.onMute,
    required this.onSeek,
    required this.onAudio,
    required this.onBack,
  });

  String _formatDuration(Duration d) {
    final hh = d.inHours.toString().padLeft(2, '0');
    final mm = (d.inMinutes % 60).toString().padLeft(2, '0');
    final ss = (d.inSeconds % 60).toString().padLeft(2, '0');
    return d.inHours > 0 ? "$hh:$mm:$ss" : "$mm:$ss";
  }

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

          // Play/Pause central con botón Mute
          Center(
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                IconButton(
                  icon: Icon(isMuted ? Icons.volume_off : Icons.volume_up, color: Colors.white70, size: 32),
                  onPressed: onMute,
                ),
                const SizedBox(width: 30),
                IconButton(
                  icon: Icon(
                    isPlaying ? Icons.pause_circle_filled : Icons.play_circle_filled,
                    color: Colors.white,
                    size: 84,
                  ),
                  onPressed: onTogglePlay,
                ),
                const SizedBox(width: 30),
                IconButton(
                  icon: const Icon(Icons.settings, color: Colors.white70, size: 32),
                  onPressed: onAudio,
                ),
              ],
            ),
          ),

          // Barra de progreso y tiempo
          Padding(
            padding: const EdgeInsets.only(bottom: 30, left: 24, right: 24),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                if (channel.type != 'live' && duration.inSeconds > 0) ...[
                  SliderTheme(
                    data: SliderTheme.of(context).copyWith(
                      trackHeight: 4,
                      thumbShape: const RoundSliderThumbShape(enabledThumbRadius: 6),
                      overlayShape: const RoundSliderOverlayShape(overlayRadius: 14),
                      activeTrackColor: AppTheme.primaryRed,
                      inactiveTrackColor: Colors.white24,
                      thumbColor: AppTheme.primaryRed,
                    ),
                    child: Slider(
                      value: position.inSeconds.toDouble().clamp(0.0, duration.inSeconds.toDouble()),
                      max: duration.inSeconds.toDouble(),
                      onChanged: (v) => onSeek(Duration(seconds: v.toInt())),
                    ),
                  ),
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(_formatDuration(position), style: const TextStyle(color: Colors.white70, fontSize: 12)),
                        Text(_formatDuration(duration), style: const TextStyle(color: Colors.white70, fontSize: 12)),
                      ],
                    ),
                  ),
                ] else ...[
                   Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                        decoration: BoxDecoration(
                          color: AppTheme.primaryRed,
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: const Row(
                          children: [
                            CircleAvatar(radius: 3, backgroundColor: Colors.white),
                            SizedBox(width: 8),
                            Text('EN VIVO', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12)),
                          ],
                        ),
                      ),
                    ],
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}
