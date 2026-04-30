import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:ott_app/features/live_tv/data/sources/live_tv_remote_source.dart';
import 'package:ott_app/features/vod/data/sources/vod_remote_source.dart';
import 'package:ott_app/shared/models/channel_model.dart';
import 'package:ott_app/core/network/dio_client.dart';
import 'package:ott_app/features/auth/presentation/providers/auth_provider.dart';

final liveTvDataSourceProvider = Provider((ref) {
  return LiveTvRemoteDataSource(ref.watch(dioClientProvider));
});

final vodDataSourceProvider = Provider((ref) {
  return VodRemoteDataSource(ref.watch(dioClientProvider));
});

class HomeState {
  final List<ChannelModel> featuredChannels;
  final List<ChannelModel> recentChannels;
  final List<ChannelModel> movies;
  final List<ChannelModel> series;
  final List<CategoryModel> categories;
  final bool isLoading;
  final String? error;

  HomeState({
    this.featuredChannels = const [],
    this.recentChannels = const [],
    this.movies = const [],
    this.series = const [],
    this.categories = const [],
    this.isLoading = false,
    this.error,
  });

  HomeState copyWith({
    List<ChannelModel>? featuredChannels,
    List<ChannelModel>? recentChannels,
    List<ChannelModel>? movies,
    List<ChannelModel>? series,
    List<CategoryModel>? categories,
    bool? isLoading,
    String? error,
  }) {
    return HomeState(
      featuredChannels: featuredChannels ?? this.featuredChannels,
      recentChannels: recentChannels ?? this.recentChannels,
      movies: movies ?? this.movies,
      series: series ?? this.series,
      categories: categories ?? this.categories,
      isLoading: isLoading ?? this.isLoading,
      error: error,
    );
  }
}

class HomeNotifier extends StateNotifier<HomeState> {
  final LiveTvRemoteDataSource _liveTvDataSource;
  final VodRemoteDataSource _vodDataSource;

  HomeNotifier(this._liveTvDataSource, this._vodDataSource) : super(HomeState()) {
    initHome();
  }

  Future<void> initHome() async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      // 1. Cargar canales live + películas M3U + series M3U + categorías en paralelo
      final results = await Future.wait([
        _liveTvDataSource.getChannels(type: 'live').timeout(const Duration(seconds: 8), onTimeout: () => []),
        _liveTvDataSource.getChannels(type: 'movie').timeout(const Duration(seconds: 8), onTimeout: () => []),
        _liveTvDataSource.getChannels(type: 'series').timeout(const Duration(seconds: 8), onTimeout: () => []),
        _liveTvDataSource.getCategories(type: 'live').timeout(const Duration(seconds: 5), onTimeout: () => []),
      ]);

      final List<ChannelModel> channels  = results[0] as List<ChannelModel>;
      final List<ChannelModel> m3uMovies  = results[1] as List<ChannelModel>;
      final List<ChannelModel> m3uSeries  = results[2] as List<ChannelModel>;
      final List<CategoryModel> categories = results[3] as List<CategoryModel>;

      // 2. Intentar cargar catálogos de Stremio (opcional, no bloquea)
      List<ChannelModel> stremioMovies = [];
      List<ChannelModel> stremioSeries = [];
      try {
        final catalogs = await _vodDataSource.getStremioCatalogs()
            .timeout(const Duration(seconds: 3));
        final futures = catalogs.take(4).map((cat) =>
          _vodDataSource.getStremioItems(
            baseUrl: cat['addon_url'],
            type: cat['type'],
            id: cat['id'],
          ).timeout(const Duration(seconds: 4), onTimeout: () => [])
        ).toList();
        final itemsLists = await Future.wait(futures);
        for (int i = 0; i < itemsLists.length; i++) {
          final t = catalogs[i]['type'];
          if (t == 'movie') stremioMovies.addAll(itemsLists[i]);
          if (t == 'series') stremioSeries.addAll(itemsLists[i]);
        }
      } catch (_) {
        // Stremio es opcional — si falla, la app sigue con contenido M3U
      }

      // 3. Combinar: primero el contenido M3U (reproducible directo),
      //    luego el de Stremio (requiere debrid para reproducir)
      final allMovies  = [...m3uMovies,  ...stremioMovies];
      final allSeries  = [...m3uSeries,  ...stremioSeries];

      state = state.copyWith(
        isLoading: false,
        featuredChannels: [
          ...m3uMovies.where((c) => c.logo != null).take(5),
          ...channels.where((c) => c.logo != null).take(5),
        ],
        recentChannels: channels,
        movies: allMovies,
        series: allSeries,
        categories: categories,
      );
    } catch (e) {
      if (mounted) {
        state = state.copyWith(isLoading: false, error: 'Error al cargar contenido.');
      }
    }
  }
}

final homeProvider = StateNotifierProvider.autoDispose<HomeNotifier, HomeState>((ref) {
  return HomeNotifier(
    ref.watch(liveTvDataSourceProvider),
    ref.watch(vodDataSourceProvider),
  );
});
