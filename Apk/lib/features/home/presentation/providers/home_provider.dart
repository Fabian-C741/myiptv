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
      // 1. Cargar canales en vivo
      final channels = await _liveTvDataSource.getChannels(type: 'live');
      final categories = await _liveTvDataSource.getCategories(type: 'live');
      
      // 2. Cargar VOD de Stremio (Mínimo 1 catálogo por defecto para empezar)
      List<ChannelModel> stremioMovies = [];
      List<ChannelModel> stremioSeries = [];
      
      final catalogs = await _vodDataSource.getStremioCatalogs();
      for (final cat in catalogs.take(2)) {
         final items = await _vodDataSource.getStremioItems(
            baseUrl: cat['addon_url'],
            type: cat['type'],
            id: cat['id'],
         );
         if (cat['type'] == 'movie') stremioMovies.addAll(items);
         if (cat['type'] == 'series') stremioSeries.addAll(items);
      }

      state = state.copyWith(
        isLoading: false,
        featuredChannels: [...stremioMovies.take(3), ...stremioSeries.take(2)], 
        recentChannels: channels.take(10).toList(),
        movies: stremioMovies,
        series: stremioSeries,
        categories: categories,
      );
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
    }
  }
}

final homeProvider = StateNotifierProvider<HomeNotifier, HomeState>((ref) {
  return HomeNotifier(
    ref.watch(liveTvDataSourceProvider),
    ref.watch(vodDataSourceProvider),
  );
});
