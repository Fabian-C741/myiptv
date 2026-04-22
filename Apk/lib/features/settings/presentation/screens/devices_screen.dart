import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../providers/settings_provider.dart';
import '../../../../core/theme/app_theme.dart';

class DevicesScreen extends ConsumerWidget {
  const DevicesScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final settingsState = ref.watch(settingsProvider);

    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(title: const Text('Dispositivos Conectados')),
      body: settingsState.isLoading
          ? const Center(child: CircularProgressIndicator(color: AppTheme.primaryRed))
          : ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: settingsState.devices.length,
              itemBuilder: (context, index) {
                final device = settingsState.devices[index];
                return Card(
                  color: Colors.grey[900],
                  margin: const EdgeInsets.only(bottom: 12),
                  child: ListTile(
                    leading: Icon(
                      device.type == 'android_tv' ? Icons.tv : Icons.smartphone,
                      color: device.isCurrent ? AppTheme.primaryRed : Colors.white,
                    ),
                    title: Text(
                      device.name,
                      style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
                    ),
                    subtitle: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('${device.os ?? "Desconocido"} • ${device.location ?? "Ubicación desconocida"}', style: const TextStyle(color: Colors.grey, fontSize: 12)),
                        Text('IP: ${device.ip ?? "0.0.0.0"}', style: const TextStyle(color: Colors.grey, fontSize: 10)),
                      ],
                    ),
                    trailing: device.isCurrent
                        ? const Chip(label: Text('Este dispositivo', style: TextStyle(fontSize: 10)), backgroundColor: Colors.black)
                        : IconButton(
                            icon: const Icon(Icons.logout, color: Colors.blueGrey),
                            onPressed: () => ref.read(settingsProvider.notifier).revokeDevice(device.id),
                          ),
                  ),
                );
              },
            ),
    );
  }
}
