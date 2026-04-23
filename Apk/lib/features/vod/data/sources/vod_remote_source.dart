import '../../../../core/network/dio_client.dart';
import '../../../../core/config/app_config.dart';
import 'package:ott_app/shared/models/channel_model.dart';
import 'package:dio/dio.dart';

class VodRemoteDataSource {
  final DioClient _dioClient;

  VodRemoteDataSource(this._dioClient);

  /// Obtiene los catálogos disponibles de Stremio (Addons conectados)
  Future<List<Map<String, dynamic>>> getStremioCatalogs() async {
    try {
      final response = await _dioClient.instance.get('/vod/stremio/catalogs');
      return List<Map<String, dynamic>>.from(response.data);
    } catch (e) {
      return [];
    }
  }

  /// Obtiene los items de un catálogo específico
  Future<List<ChannelModel>> getStremioItems({
    required String baseUrl,
    required String type,
    required String id,
  }) async {
    try {
      final response = await _dioClient.instance.get(
        '/vod/stremio/items',
        queryParameters: {
          'base_url': baseUrl,
          'type': type,
          'id': id,
        },
      );
      
      final List metas = response.data['metas'] ?? [];
      return metas.map((m) => ChannelModel(
        id: 0, // No tiene ID en nuestra DB aún
        name: m['name'] ?? '',
        streamUrl: '', // Se obtiene al abrirlo
        logo: m['poster'] ?? m['logo'],
        description: m['description'] ?? '',
        type: type == 'movie' ? 'movie' : 'series',
        isExternal: true,
        externalId: m['id'],
        externalSource: 'stremio',
        addonBaseUrl: baseUrl,
      )).toList();
    } catch (e) {
      return [];
    }
  }
}
