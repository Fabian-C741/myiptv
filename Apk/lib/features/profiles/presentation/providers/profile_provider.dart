import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:ott_app/features/profiles/data/models/profile_model.dart';
import 'package:ott_app/features/profiles/data/sources/profile_remote_source.dart';
import '../../../../features/auth/presentation/providers/auth_provider.dart';
import '../../../../core/storage/secure_storage_service.dart';

final profileDataSourceProvider = Provider((ref) {
  return ProfileRemoteDataSource(ref.watch(dioClientProvider));
});

// Estado de Selección de Perfil
class ProfileState {
  final List<ProfileModel> profiles;
  final ProfileModel? selectedProfile;
  final bool isLoading;
  final String? error;
  final bool isPinVerified;

  ProfileState({
    this.profiles = const [],
    this.selectedProfile,
    this.isLoading = false,
    this.error,
    this.isPinVerified = false,
  });

  ProfileState copyWith({
    List<ProfileModel>? profiles,
    ProfileModel? selectedProfile,
    bool? isLoading,
    String? error,
    bool? isPinVerified,
  }) {
    return ProfileState(
      profiles: profiles ?? this.profiles,
      selectedProfile: selectedProfile ?? this.selectedProfile,
      isLoading: isLoading ?? this.isLoading,
      error: error,
      isPinVerified: isPinVerified ?? this.isPinVerified,
    );
  }
}

class ProfileNotifier extends StateNotifier<ProfileState> {
  final ProfileRemoteDataSource _dataSource;
  final SecureStorageService _storage;

  ProfileNotifier(this._dataSource, this._storage) : super(ProfileState()) {
    loadProfiles();
  }

  Future<void> loadProfiles() async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final profiles = await _dataSource.getProfiles();
      state = state.copyWith(isLoading: false, profiles: profiles);
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
    }
  }

  void selectProfile(ProfileModel profile) {
    state = state.copyWith(selectedProfile: profile, isPinVerified: !profile.hasPin);
  }

  Future<bool> verifyPin(String pin) async {
    if (state.selectedProfile == null) return false;
    
    state = state.copyWith(isLoading: true, error: null);
    try {
      final success = await _dataSource.verifyPin(state.selectedProfile!.id, pin);
      if (success) {
        await _storage.saveProfileId(state.selectedProfile!.id);
        state = state.copyWith(isLoading: false, isPinVerified: true);
      } else {
        state = state.copyWith(isLoading: false, error: 'PIN Incorrecto');
      }
      return success;
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
      return false;
    }
  }

  Future<void> updateProfile(int id, Map<String, dynamic> data) async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final updatedProfile = await _dataSource.updateProfile(id, data);
      final profiles = state.profiles.map((p) => p.id == id ? updatedProfile : p).toList();
      state = state.copyWith(isLoading: false, profiles: profiles);
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
    }
  }

  Future<void> deleteProfile(int id) async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      await _dataSource.deleteProfile(id);
      final profiles = state.profiles.where((p) => p.id != id).toList();
      state = state.copyWith(isLoading: false, profiles: profiles, selectedProfile: null);
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
    }
  }

  Future<void> addProfile(String name) async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final newProfile = await _dataSource.createProfile(name);
      final profiles = [...state.profiles, newProfile];
      state = state.copyWith(isLoading: false, profiles: profiles);
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
    }
  }

  Future<String?> uploadAvatar(int profileId, dynamic imageFile) async {
    try {
      final url = await _dataSource.uploadAvatar(profileId, imageFile);
      // Actualizar avatar en el perfil local
      final profiles = state.profiles.map((p) {
        if (p.id == profileId) {
          return ProfileModel(
            id: p.id,
            name: p.name,
            avatar: url,
            isChild: p.isChild,
            hasPin: p.hasPin,
          );
        }
        return p;
      }).toList();
      state = state.copyWith(profiles: profiles);
      return url;
    } catch (e) {
      return null;
    }
  }

  void logoutProfile() {
    state = state.copyWith(selectedProfile: null, isPinVerified: false);
  }
}

final profileProvider = StateNotifierProvider<ProfileNotifier, ProfileState>((ref) {
  return ProfileNotifier(
    ref.watch(profileDataSourceProvider),
    ref.watch(secureStorageProvider),
  );
});
