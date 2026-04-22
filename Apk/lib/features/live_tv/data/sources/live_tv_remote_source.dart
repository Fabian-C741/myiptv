import 'package:dio/dio.dart';
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
      
      // Handle Laravel pagination if needed, or direct list
      final List data = (response.data is Map) ? response.data['data'] : response.data;
      return data.map((json) => ChannelModel.fromJson(json)).toList();
    } on DioException catch (_) {
      throw 'Error al cargar contenido';
    }
  }

  Future<List<CategoryModel>> getCategories({String type = 'live'}) async {
    try {
      final response = await _dioClient.instance.get(
        AppConfig.groups,
        queryParameters: {'type': type},
      );
      final List data = response.data;
      return data.map((json) => CategoryModel.fromJson(json)).toList();
    } on DioException catch (_) {
      throw 'Error al cargar categorías';
    }
  }

  Future<ChannelModel> getSeriesDetails(int id) async {
    try {
      final response = await _dioClient.instance.get('${AppConfig.series}/$id');
      return ChannelModel.fromJson(response.data);
    } on DioException catch (_) {
      throw 'Error al cargar detalles de la serie';
    }
  }
}
