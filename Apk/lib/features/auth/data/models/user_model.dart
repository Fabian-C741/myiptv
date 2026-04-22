class UserModel {
  final int id;
  final String name;
  final String email;
  final String role;
  final int maxDevices;

  UserModel({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    required this.maxDevices,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id'],
      name: json['name'],
      email: json['email'],
      role: json['role'] ?? 'user',
      maxDevices: json['max_devices'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'role': role,
      'max_devices': maxDevices,
    };
  }
}
