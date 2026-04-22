class ChannelModel {
  final int id;
  final String name;
  final String? type; // live, movie, series
  final String? streamUrl;
  final String? logo;
  final int? groupId;
  final bool isAdult;
  final String? description;
  final String? releaseDate;
  final String? rating;
  final String? duration;
  final String? backdrop;
  final int? tmdbId;
  final List<SeasonModel>? seasons;
  
  // Limpiador de Nombres (Meta-Cleaner V1)
  String get displayName {
    return name
      .replaceAll(RegExp(r'\|[A-Z]{2}\|'), '') // Quita |ES|, |MX|
      .replaceAll(RegExp(r'\[.*?\]'), '')      // Quita [HD], [SD], [4K]
      .replaceAll(RegExp(r'\(.*?\)'), '')      // Quita (LAT), (SUB), (Premium)
      .replaceAll(RegExp(r'^[0-9]+'), '')      // Quita números iniciales si existen
      .trim();
  }

  ChannelModel({
    required this.id,
    required this.name,
    this.type = 'live',
    this.streamUrl,
    this.logo,
    this.groupId,
    this.isAdult = false,
    this.description,
    this.releaseDate,
    this.rating,
    this.duration,
    this.backdrop,
    this.tmdbId,
    this.seasons,
  });

  factory ChannelModel.fromJson(Map<String, dynamic> json) {
    return ChannelModel(
      id: json['id'],
      name: json['name'],
      type: json['type'],
      streamUrl: json['stream_url'],
      logo: json['logo'],
      groupId: json['channel_group_id'],
      isAdult: json['is_adult'] ?? false,
      description: json['description'],
      releaseDate: json['release_date'],
      rating: json['rating']?.toString(),
      duration: json['duration'],
      backdrop: json['backdrop'],
      tmdbId: json['tmdb_id'],
      seasons: json['seasons'] != null
          ? (json['seasons'] as List)
              .map((i) => SeasonModel.fromJson(i))
              .toList()
          : null,
    );
  }
}

class CategoryModel {
  final String id;
  final String name;
  final String type;

  CategoryModel({required this.id, required this.name, this.type = 'live'});

  factory CategoryModel.fromJson(Map<String, dynamic> json) {
    return CategoryModel(
      id: json['id'].toString(),
      name: json['name'],
      type: json['type'] ?? 'live',
    );
  }
}

class SeasonModel {
  final int id;
  final String name;
  final int seasonNumber;
  final List<EpisodeModel> episodes;

  SeasonModel({
    required this.id,
    required this.name,
    required this.seasonNumber,
    required this.episodes,
  });

  factory SeasonModel.fromJson(Map<String, dynamic> json) {
    return SeasonModel(
      id: json['id'],
      name: json['name'],
      seasonNumber: json['season_number'],
      episodes: json['episodes'] != null
          ? (json['episodes'] as List)
              .map((i) => EpisodeModel.fromJson(i))
              .toList()
          : [],
    );
  }
}

class EpisodeModel {
  final int id;
  final String name;
  final int episodeNumber;
  final String? streamUrl;
  final String? duration;

  EpisodeModel({
    required this.id,
    required this.name,
    required this.episodeNumber,
    this.streamUrl,
    this.duration,
  });

  factory EpisodeModel.fromJson(Map<String, dynamic> json) {
    return EpisodeModel(
      id: json['id'],
      name: json['name'],
      episodeNumber: json['episode_number'],
      streamUrl: json['stream_url'],
      duration: json['duration'],
    );
  }
}
