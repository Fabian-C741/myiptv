import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../core/network/dio_client.dart';
import '../../../../core/config/app_config.dart';
import 'package:ott_app/shared/models/channel_model.dart';

class LiveTvRemoteDataSource {
  final DioClient _dioClient;

  LiveTvRemoteDataSource(this._dioClient);

  Future<List<ChannelModel>> getChannels({String type = 'live', String? groupId}) async {
    try {
      final url = groupId != null ? '${AppConfig.groups}/$groupId' : AppConfig.channels;
      final response = await _dioClient.instance.get(
        url,
        queryParameters: {'type': type},
      );

      // El servidor puede responder con una lista directa O con paginación Laravel {data: [...]}
      List rawList;
      if (response.data is Map) {
        rawList = response.data['data'] ?? [];
      } else if (response.data is List) {
        rawList = response.data;
      } else {
        rawList = [];
      }

      return rawList.map((json) => ChannelModel.fromJson(json as Map<String, dynamic>)).toList();
    } on DioException catch (e) {
      throw 'Error al cargar contenido: ${e.message}';
    } catch (e) {
      throw 'Error inesperado: $e';
    }
  }

  Future<List<CategoryModel>> getCategories({String type = 'live'}) async {
    try {
      final response = await _dioClient.instance.get(
        AppConfig.groups,
        queryParameters: {'type': type},
      );
      // Misma lógica: puede venir como lista o como mapa paginado
      final List data = (response.data is Map)
          ? (response.data['data'] ?? [])
          : (response.data is List ? response.data : []);
      return data.map((json) => CategoryModel.fromJson(json as Map<String, dynamic>)).toList();
    } on DioException catch (e) {
      throw 'Error al cargar categorías: ${e.message}';
    }
  }

  Future<ChannelModel> getSeriesDetails(int id) async {
    try {
      final response = await _dioClient.instance.get('${AppConfig.series}/$id');
      return ChannelModel.fromJson(response.data);
    } on DioException catch (e) {
      throw 'Error al cargar detalles: ${e.message}';
    }
  }
}
