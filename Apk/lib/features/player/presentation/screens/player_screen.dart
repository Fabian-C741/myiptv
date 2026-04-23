import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
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
  bool _isLocked = false;
  double _brightness = 0.5;
  double _volume = 1.0;
  double _playbackSpeed = 1.0;
  DateTime? _lastActivity;

  @override
  void initState() {
    super.initState();
    // Ocultar barras del sistema
    SystemChrome.setEnabledSystemUIMode(SystemUiMode.immersiveSticky);
    SystemChrome.setPreferredOrientations([
        DeviceOrientation.landscapeLeft,
        DeviceOrientation.landscapeRight,
    ]);

    final player = ref.read(playerProvider('global')).player;
    _videoController = VideoController(player);
    _volume = player.state.volume / 100;

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
    final player = ref.read(playerProvider('global')).player;
    player.stop(); // Detener el video inmediatamente
    
    SystemChrome.setEnabledSystemUIMode(SystemUiMode.edgeToEdge);
    SystemChrome.setPreferredOrientations([
        DeviceOrientation.portraitUp,
    ]);
    super.dispose();
  }

  void _showAudioPicker(BuildContext context, PlayerState playerState) {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (_) => Container(
        decoration: const BoxDecoration(
          color: Color(0xFF141414),
          borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
        ),
        padding: const EdgeInsets.symmetric(vertical: 20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(width: 40, height: 4, decoration: BoxDecoration(color: Colors.white24, borderRadius: BorderRadius.circular(2))),
            const SizedBox(height: 20),
            const Text('Audio y Subtítulos', style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
            const SizedBox(height: 20),
            Flexible(
              child: ListView.builder(
                shrinkWrap: true,
                itemCount: playerState.tracks.audio.length,
                itemBuilder: (_, i) {
                  final track = playerState.tracks.audio[i];
                  final isActive = playerState.activeAudioTrack?.id == track.id;
                  return ListTile(
                    leading: Icon(Icons.check, color: isActive ? Colors.white : Colors.transparent),
                    title: Text(
                        track.language?.toUpperCase() ?? 'Audio ${i+1}',
                        style: TextStyle(color: isActive ? Colors.white : Colors.grey, fontWeight: isActive ? FontWeight.bold : FontWeight.normal)
                    ),
                    onTap: () {
                      ref.read(playerProvider('global').notifier).setAudioTrack(track);
                      Navigator.pop(context);
                    },
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showSpeedPicker(BuildContext context, Player player) {
    final speeds = [0.5, 0.75, 1.0, 1.25, 1.5, 2.0];
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (_) => Container(
        decoration: const BoxDecoration(
          color: Color(0xFF141414),
          borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
        ),
        child: ListView(
          shrinkWrap: true,
          children: speeds.map((s) => ListTile(
            title: Text('${s}x', style: TextStyle(color: _playbackSpeed == s ? Colors.white : Colors.grey, fontWeight: _playbackSpeed == s ? FontWeight.bold : FontWeight.normal)),
            onTap: () {
              player.setRate(s);
              setState(() => _playbackSpeed = s);
              Navigator.pop(context);
            },
          )).toList(),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final playerState = ref.watch(playerProvider('global'));
    final player = playerState.player;

    return Scaffold(
      backgroundColor: Colors.black,
      body: GestureDetector(
        behavior: HitTestBehavior.opaque,
        onTap: () {
            if (_isLocked) {
                setState(() => _showControls = !_showControls);
            } else {
                _resetTimer();
            }
        },
        onVerticalDragUpdate: _isLocked ? null : (details) {
            final double delta = details.primaryDelta! / MediaQuery.of(context).size.height;
            if (details.globalPosition.dx < MediaQuery.of(context).size.width / 2) {
                // Brillo (Lado izquierdo)
                setState(() => _brightness = (_brightness - delta).clamp(0.0, 1.0));
            } else {
                // Volumen (Lado derecho)
                setState(() {
                    _volume = (_volume - delta).clamp(0.0, 1.0);
                    player.setVolume(_volume * 100);
                });
            }
            _resetTimer();
        },
        child: Stack(
          children: [
            // Video
            Center(child: Video(controller: _videoController, fill: Colors.black)),

            // Overlay de Brillo/Volumen (indicadores visuales)
            if (_showControls && !_isLocked) ...[
                _SideIndicator(icon: Icons.brightness_6, value: _brightness, isLeft: true),
                _SideIndicator(icon: Icons.volume_up, value: _volume, isLeft: false),
            ],

            // Buffering / Loading
            if (playerState.isBuffering)
                const Center(child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2)),

            // Controles Netflix Style
            if (_showControls) ...[
                // Top Bar
                Positioned(
                    top: 0, left: 0, right: 0,
                    child: _TopBar(
                        title: widget.channel.displayName,
                        isLocked: _isLocked,
                        onBack: () => Navigator.pop(context),
                        onLock: () => setState(() => _isLocked = !_isLocked),
                    ),
                ),

                // Center Controls
                if (!_isLocked)
                    Center(
                        child: Row(
                            mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                            children: [
                                _ControlButton(icon: Icons.replay_10, size: 48, onTap: () => player.seek(player.state.position - const Duration(seconds: 10))),
                                IconButton(
                                    icon: Icon(playerState.isPlaying ? Icons.pause : Icons.play_arrow, color: Colors.white, size: 80),
                                    onPressed: () => ref.read(playerProvider('global').notifier).togglePlay(),
                                ),
                                _ControlButton(icon: Icons.forward_10, size: 48, onTap: () => player.seek(player.state.position + const Duration(seconds: 10))),
                            ],
                        ),
                    ),

                // Bottom Bar
                if (!_isLocked)
                    Positioned(
                        bottom: 0, left: 0, right: 0,
                        child: _BottomBar(
                            position: player.state.position,
                            duration: player.state.duration,
                            speed: _playbackSpeed,
                            isLive: widget.channel.type == 'live',
                            onSeek: (d) => player.seek(d),
                            onAudio: () => _showAudioPicker(context, playerState),
                            onSpeed: () => _showSpeedPicker(context, player),
                        ),
                    ),
            ],
          ],
        ),
      ),
    );
  }
}

class _TopBar extends StatelessWidget {
    final String title;
    final bool isLocked;
    final VoidCallback onBack;
    final VoidCallback onLock;

    const _TopBar({required this.title, required this.isLocked, required this.onBack, required this.onLock});

    @override
    Widget build(BuildContext context) {
        return Container(
            height: 100,
            padding: const EdgeInsets.symmetric(horizontal: 20),
            decoration: BoxDecoration(gradient: LinearGradient(begin: Alignment.topCenter, end: Alignment.bottomCenter, colors: [Colors.black87, Colors.transparent])),
            child: Row(
                children: [
                    if (!isLocked) IconButton(icon: const Icon(Icons.arrow_back, color: Colors.white), onPressed: onBack),
                    const SizedBox(width: 10),
                    if (!isLocked) Expanded(child: Text(title, style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.w500))),
                    IconButton(icon: Icon(isLocked ? Icons.lock : Icons.lock_open, color: Colors.white70), onPressed: onLock),
                ],
            ),
        );
    }
}

class _BottomBar extends StatelessWidget {
    final Duration position;
    final Duration duration;
    final double speed;
    final bool isLive;
    final Function(Duration) onSeek;
    final VoidCallback onAudio;
    final VoidCallback onSpeed;

    const _BottomBar({required this.position, required this.duration, required this.speed, required this.isLive, required this.onSeek, required this.onAudio, required this.onSpeed});

    String _format(Duration d) => "${d.inMinutes}:${(d.inSeconds % 60).toString().padLeft(2, '0')}";

    @override
    Widget build(BuildContext context) {
        return Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(gradient: LinearGradient(begin: Alignment.bottomCenter, end: Alignment.topCenter, colors: [Colors.black87, Colors.transparent])),
            child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                    if (!isLive) ...[
                        SliderTheme(
                            data: SliderTheme.of(context).copyWith(activeTrackColor: Colors.red, inactiveTrackColor: Colors.white24, thumbColor: Colors.red, trackHeight: 3),
                            child: Slider(
                                value: position.inSeconds.toDouble().clamp(0.0, duration.inSeconds.toDouble()),
                                max: duration.inSeconds.toDouble() > 0 ? duration.inSeconds.toDouble() : 1.0,
                                onChanged: (v) => onSeek(Duration(seconds: v.toInt())),
                            ),
                        ),
                        Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                                Text(_format(position), style: const TextStyle(color: Colors.white70, fontSize: 12)),
                                Text(_format(duration), style: const TextStyle(color: Colors.white70, fontSize: 12)),
                            ],
                        ),
                    ],
                    const SizedBox(height: 10),
                    Row(
                        mainAxisAlignment: MainAxisAlignment.spaceAround,
                        children: [
                            _BottomAction(icon: Icons.speed, label: '${speed}x', onTap: onSpeed),
                            _BottomAction(icon: Icons.chat_bubble_outline, label: 'Audio y subtítulos', onTap: onAudio),
                            _BottomAction(icon: Icons.video_library_outlined, label: 'Episodios', onTap: () {}),
                            _BottomAction(icon: Icons.forward_10, label: 'Siguiente', onTap: () {}),
                        ],
                    ),
                ],
            ),
        );
    }
}

class _BottomAction extends StatelessWidget {
    final IconData icon;
    final String label;
    final VoidCallback onTap;
    const _BottomAction({required this.icon, required this.label, required this.onTap});

    @override
    Widget build(BuildContext context) {
        return InkWell(
            onTap: onTap,
            child: Column(
                children: [
                    Icon(icon, color: Colors.white, size: 24),
                    const SizedBox(height: 4),
                    Text(label, style: const TextStyle(color: Colors.white, fontSize: 10)),
                ],
            ),
        );
    }
}

class _SideIndicator extends StatelessWidget {
    final IconData icon;
    final double value;
    final bool isLeft;
    const _SideIndicator({required this.icon, required this.value, required this.isLeft});

    @override
    Widget build(BuildContext context) {
        return Positioned(
            left: isLeft ? 40 : null,
            right: !isLeft ? 40 : null,
            top: MediaQuery.of(context).size.height * 0.3,
            bottom: MediaQuery.of(context).size.height * 0.3,
            child: Column(
                children: [
                    Icon(icon, color: Colors.white70, size: 20),
                    const SizedBox(height: 10),
                    Expanded(
                        child: Container(
                            width: 4,
                            decoration: BoxDecoration(color: Colors.white24, borderRadius: BorderRadius.circular(2)),
                            child: Stack(
                                alignment: Alignment.bottomCenter,
                                children: [
                                    Container(
                                        width: 4,
                                        height: (MediaQuery.of(context).size.height * 0.4) * value,
                                        decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(2)),
                                    ),
                                ],
                            ),
                        ),
                    ),
                ],
            ),
        );
    }
}

class _ControlButton extends StatelessWidget {
    final IconData icon;
    final double size;
    final VoidCallback onTap;
    const _ControlButton({required this.icon, required this.size, required this.onTap});

    @override
    Widget build(BuildContext context) {
        return IconButton(
            icon: Icon(icon, color: Colors.white, size: size),
            onPressed: onTap,
        );
    }
}

