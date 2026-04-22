class AppConfig {
  static const String appName = 'Electrofabiptv';
  static const String baseUrl = 'https://streaming-iptv.kcrsf.com/api';

  // Endpoints
  static const String login = '/login';
  static const String logout = '/logout';
  static const String profiles = '/profiles';
  static const String channels = '/channels';
  static const String groups = '/channels/groups';
  static const String series = '/series';
  static const String epgCurrent = '/channels';
  static const String favorites = '/favorites';
  static const String devices = '/devices';
  static const String brandConfig = '/app/config';
  static const String externalSources = '/external-sources';

  // Configuración de dispositivo
  static const int defaultDeviceLimit = 3;
}
