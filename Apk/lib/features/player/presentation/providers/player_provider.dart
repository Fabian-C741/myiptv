import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:media_kit/media_kit.dart';
import 'package:ott_app/shared/models/channel_model.dart';

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

  PlayerNotifier() : super(PlayerState(player: Player(configuration: const PlayerConfiguration(
    bufferSize: 32 * 1024 * 1024, // 32 MB buffer para carga rápida
  )))) {
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
      if (mounted) state = state.copyWith(tracks: tracks);
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
      await state.player.open(Media(url));
      await state.player.play();
    } catch (e) {
      if (mounted) state = state.copyWith(error: 'Error: $e');
    }
  }

  void togglePlay() => state.player.playOrPause();

  void stop() {
    state.player.pause();
  }

  Future<void> retry() async {
    if (state.currentChannel != null) {
      state = state.copyWith(retryCount: 0, error: null, isBuffering: true);
      await _doPlay(state.currentChannel!);
    }
  }

  void setAudioTrack(AudioTrack track) {
    state.player.setAudioTrack(track);
    state = state.copyWith(activeAudioTrack: track);
  }

  @override
  void dispose() {
    state.player.dispose();
    super.dispose();
  }
}

final playerProvider = StateNotifierProvider.family<PlayerNotifier, PlayerState, String>((ref, id) {
  return PlayerNotifier();
});
