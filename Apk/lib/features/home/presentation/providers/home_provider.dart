import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:ott_app/features/live_tv/data/sources/live_tv_remote_source.dart';
import 'package:ott_app/shared/models/channel_model.dart';
import 'package:ott_app/features/auth/presentation/providers/auth_provider.dart';

final liveTvDataSourceProvider = Provider((ref) {
  return LiveTvRemoteDataSource(ref.watch(dioClientProvider));
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
  final LiveTvRemoteDataSource _dataSource;

  HomeNotifier(this._dataSource) : super(HomeState()) {
    initHome();
  }

  Future<void> initHome() async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final channels = await _dataSource.getChannels(type: 'live');
      final movies = await _dataSource.getChannels(type: 'movie');
      final series = await _dataSource.getChannels(type: 'series');
      final categories = await _dataSource.getCategories(type: 'live');
      
      state = state.copyWith(
        isLoading: false,
        featuredChannels: [...movies.take(3), ...series.take(2)], // Destacamos VOD
        recentChannels: channels.take(10).toList(),
        movies: movies,
        series: series,
        categories: categories,
      );
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
    }
  }
}

final homeProvider = StateNotifierProvider<HomeNotifier, HomeState>((ref) {
  return HomeNotifier(ref.watch(liveTvDataSourceProvider));
});
