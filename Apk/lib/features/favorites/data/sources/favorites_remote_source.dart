import 'package:dio/dio.dart';
import '../../../../core/network/dio_client.dart';
import '../../../../core/config/app_config.dart';
import 'package:ott_app/shared/models/channel_model.dart';

class FavoritesRemoteDataSource {
  final DioClient _dioClient;

  FavoritesRemoteDataSource(this._dioClient);

  Future<List<ChannelModel>> getFavorites() async {
    try {
      final response = await _dioClient.instance.get(AppConfig.favorites);
      final List data = response.data;
      return data.map((json) => ChannelModel.fromJson(json)).toList();
    } on DioException catch (_) {
      throw 'Error al obtener favoritos';
    }
  }

  Future<void> toggleFavorite(int channelId) async {
    try {
      await _dioClient.instance.post(AppConfig.favorites, data: {'channel_id': channelId});
    } on DioException catch (_) {
      throw 'Error al actualizar favoritos';
    }
  }
}
