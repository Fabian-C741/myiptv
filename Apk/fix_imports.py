import os

replacements = [
    ("'../../../shared/models/channel_model.dart'", "'package:ott_app/shared/models/channel_model.dart'"),
    ("'../../live_tv/data/sources/live_tv_remote_source.dart'", "'package:ott_app/features/live_tv/data/sources/live_tv_remote_source.dart'"),
    ("'../../auth/presentation/providers/auth_provider.dart'", "'package:ott_app/features/auth/presentation/providers/auth_provider.dart'"),
    ("'../../../core/theme/app_theme.dart'", "'package:ott_app/core/theme/app_theme.dart'"),
    ("'../../home/presentation/providers/home_provider.dart'", "'package:ott_app/features/home/presentation/providers/home_provider.dart'"),
    ("'../data/models/profile_model.dart'", "'package:ott_app/features/profiles/data/models/profile_model.dart'"),
    ("'../data/sources/profile_remote_source.dart'", "'package:ott_app/features/profiles/data/sources/profile_remote_source.dart'"),
    ("'../models/device_model.dart'", "'package:ott_app/features/settings/data/models/device_model.dart'")
]

for root, _, files in os.walk('d:/Apk-tv/Apk/lib'):
    for file in files:
        if file.endswith('.dart'):
            path = os.path.join(root, file)
            with open(path, 'r', encoding='utf-8') as f:
                content = f.read()
            
            new_content = content
            for old, new in replacements:
                new_content = new_content.replace(old, new)
            
            if new_content != content:
                with open(path, 'w', encoding='utf-8') as f:
                    f.write(new_content)
                print(f'Fixed imports in {path}')
