import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:media_kit/media_kit.dart';
import 'package:ott_app/shared/models/channel_model.dart';
import 'package:youtube_explode_dart/youtube_explode_dart.dart';

class PlayerState {
  final Player player;
  final bool isPlaying;
  final bool isBuffering;
  final ChannelModel? currentChannel;
  final String? error;
  final Tracks tracks;
  final AudioTrack? activeAudioTrack;
  final int retryCount;

  PlayerState({
    required this.player,
    this.isPlaying = false,
    this.isBuffering = false,
    this.currentChannel,
    this.error,
    this.tracks = const Tracks(),
    this.activeAudioTrack,
    this.retryCount = 0,
  });

  PlayerState copyWith({
    bool? isPlaying,
    bool? isBuffering,
    ChannelModel? currentChannel,
    String? error,
    Tracks? tracks,
    AudioTrack? activeAudioTrack,
    int? retryCount,
  }) {
    return PlayerState(
      player: player,
      isPlaying: isPlaying ?? this.isPlaying,
      isBuffering: isBuffering ?? this.isBuffering,
      currentChannel: currentChannel ?? this.currentChannel,
      error: error,
      tracks: tracks ?? this.tracks,
      activeAudioTrack: activeAudioTrack ?? this.activeAudioTrack,
      retryCount: retryCount ?? this.retryCount,
    );
  }
}

class PlayerNotifier extends StateNotifier<PlayerState> {
  static const int _maxRetries = 3;

  PlayerNotifier() : super(PlayerState(player: Player(
    configuration: const PlayerConfiguration(
      bufferSize: 10 * 1024 * 1024, // Aumentado a 10MB para evitar cortes en conexiones lentas
    ),
  ))) {
    // Configuración para Streaming de baja latencia
    if (state.player.platform is NativePlayer) {
      (state.player.platform as NativePlayer).setProperty('network-timeout', '10');
      (state.player.platform as NativePlayer).setProperty('cache-pause', 'no');
    }
    _initListeners();
  }

  void _initListeners() {
    state.player.stream.playing.listen((playing) {
      if (mounted) state = state.copyWith(isPlaying: playing, error: null);
    });
    state.player.stream.buffering.listen((buffering) {
      if (mounted) state = state.copyWith(isBuffering: buffering);
    });
    state.player.stream.error.listen((error) async {
      if (!mounted) return;
      if (state.retryCount < _maxRetries && state.currentChannel != null) {
        // Retry automático con delay exponencial
        await Future.delayed(Duration(seconds: state.retryCount + 1));
        if (mounted) {
          state = state.copyWith(retryCount: state.retryCount + 1, error: null);
          await _doPlay(state.currentChannel!);
        }
      } else {
        if (mounted) state = state.copyWith(error: 'No se pudo reproducir el canal. Verifica tu conexión.');
      }
    });
    state.player.stream.tracks.listen((tracks) {
      if (!mounted) return;
      state = state.copyWith(tracks: tracks);
      
      // Intento de auto-selección de audio en Español (es, spa, Spanish)
      final active = state.activeAudioTrack;
      if (active == null || active.id == 'auto') {
        for (var track in tracks.audio) {
          final lang = track.language?.toLowerCase() ?? '';
          final title = track.title?.toLowerCase() ?? '';
          if (lang.contains('es') || lang.contains('spa') || title.contains('esp')) {
            setAudioTrack(track);
            break;
          }
        }
      }
    });
    state.player.stream.track.listen((track) {
      if (mounted) state = state.copyWith(activeAudioTrack: track.audio);
    });
  }

  Future<void> playChannel(ChannelModel channel) async {
    state = state.copyWith(currentChannel: channel, error: null, isBuffering: true, retryCount: 0);
    await _doPlay(channel);
  }

  Future<void> _doPlay(ChannelModel channel) async {
    try {
      final url = channel.streamUrl ?? '';
      if (url.isEmpty) {
        state = state.copyWith(error: 'URL de stream no disponible.');
        return;
      }
      String finalUrl = url.trim();

      // Detección y extracción de YouTube
      if (finalUrl.contains('youtube.com/') || finalUrl.contains('youtu.be/')) {
        try {
          final yt = YoutubeExplode();
          // youtube_explode_dart 2.2.2 usa streams en lugar de streamsClient y acepta URLs directamente
          final manifest = await yt.videos.streams.getManifest(finalUrl);
          final streamInfo = manifest.muxed.withHighestBitrate();
          finalUrl = streamInfo.url.toString();
          yt.close();
        } catch (e) {
          print('YouTube extraction failed: $e');
        }
      }

      // Si ya hay algo reproduciendo, lo detenemos completamente
      await state.player.stop();

      // Determinamos si necesitamos headers especiales
      Map<String, String>? headers;
      if (!url.contains('youtube.com') && !url.contains('youtu.be')) {
        headers = {
          'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36',
          'Accept': '*/*',
          'Connection': 'keep-alive',
        };
      }

      final media = Media(finalUrl, httpHeaders: headers);
      await state.player.open(media, play: true);
      // Forzamos el play después de un micro-delay para asegurar que arranque
      Future.delayed(const Duration(milliseconds: 500), () {
        if (mounted && !state.isPlaying) {
          state.player.play();
        }
      });
    } catch (e) {
      if (mounted) state = state.copyWith(error: 'Error de conexión: El servidor no responde.');
    }
  }

  void togglePlay() => state.player.playOrPause();

  Future<void> stop() async {
    await state.player.stop();
  }

  Future<void> retry() async {
    if (state.currentChannel != null) {
      state = state.copyWith(retryCount: 0, error: null, isBuffering: true);
      await _doPlay(state.currentChannel!);
    }
  }

  Future<void> setAudioTrack(AudioTrack track) async {
    await state.player.setAudioTrack(track);
    if (state.isPlaying) {
        await state.player.pause();
        await Future.delayed(const Duration(milliseconds: 300));
        await state.player.play();
    }
    state = state.copyWith(activeAudioTrack: track);
  }

  @override
  void dispose() {
    state.player.dispose();
    super.dispose();
  }
}

final playerProvider = StateNotifierProvider.autoDispose.family<PlayerNotifier, PlayerState, String>((ref, id) {
  return PlayerNotifier();
});
