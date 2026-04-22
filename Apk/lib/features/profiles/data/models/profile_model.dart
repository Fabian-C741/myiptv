class ProfileModel {
  final int id;
  final String name;
  final String? avatar;
  final bool hasPin;
  final bool isChild;

  ProfileModel({
    required this.id,
    required this.name,
    this.avatar,
    this.hasPin = false,
    this.isChild = false,
  });

  factory ProfileModel.fromJson(Map<String, dynamic> json) {
    return ProfileModel(
      id: json['id'],
      name: json['name'],
      avatar: json['avatar'],
      hasPin: json['has_pin'] ?? false,
      isChild: json['is_kid'] ?? false,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'avatar': avatar,
      'has_pin': hasPin,
      'is_kid': isChild,
    };
  }
}
