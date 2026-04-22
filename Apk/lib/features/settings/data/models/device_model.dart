class DeviceModel {
  final int id;
  final String name;
  final String type;
  final String? os;
  final String? ip;
  final String? location;
  final DateTime lastSeen;
  final bool isCurrent;

  DeviceModel({
    required this.id,
    required this.name,
    required this.type,
    this.os,
    this.ip,
    this.location,
    required this.lastSeen,
    this.isCurrent = false,
  });

  factory DeviceModel.fromJson(Map<String, dynamic> json) {
    return DeviceModel(
      id: json['id'],
      name: json['name'],
      type: json['type'],
      os: json['os'],
      ip: json['ip'],
      location: json['location'],
      lastSeen: DateTime.parse(json['last_seen']),
      isCurrent: json['is_current'] ?? false,
    );
  }
}
