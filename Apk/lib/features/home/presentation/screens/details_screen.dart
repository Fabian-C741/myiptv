import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:ott_app/shared/models/channel_model.dart';
import 'package:ott_app/features/live_tv/data/sources/live_tv_remote_source.dart';
import '../providers/home_provider.dart';

class DetailsScreen extends ConsumerStatefulWidget {
  final ChannelModel content;
  const DetailsScreen({super.key, required this.content});

  @override
  ConsumerState<DetailsScreen> createState() => _DetailsScreenState();
}

class _DetailsScreenState extends ConsumerState<DetailsScreen> {
  ChannelModel? fullContent;
  bool isLoading = false;

  @override
  void initState() {
    super.initState();
    if (widget.content.type == 'series') {
      _loadSeriesDetails();
    } else {
      // Películas y live: reproducir directo sin esperar nada
      fullContent = widget.content;
    }
  }

  Future<void> _loadSeriesDetails() async {
    setState(() => isLoading = true);
    try {
      final source = ref.read(liveTvDataSourceProvider);
      final details = await source
          .getSeriesDetails(widget.content.id)
          .timeout(const Duration(seconds: 8));

      // Si tiene temporadas reales las usamos; si no, usamos el contenido original
      if (details.seasons != null && details.seasons!.isNotEmpty) {
        fullContent = details;
      } else {
        fullContent = widget.content; // Serie M3U directa con stream_url
      }
    } catch (_) {
      // Timeout o error → usar el contenido original (tiene stream_url)
      fullContent = widget.content;
    } finally {
      if (mounted) setState(() => isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final item = fullContent ?? widget.content;

    return Scaffold(
      backgroundColor: Colors.black,
      body: Stack(
        children: [
          // Backdrop
          Positioned.fill(
            child: Opacity(
              opacity: 0.4,
              child: Image.network(
                item.backdrop ?? item.logo ?? '',
                fit: BoxFit.cover,
                errorBuilder: (_, __, ___) => Container(color: Colors.grey[900]),
              ),
            ),
          ),
          // Gradient
          Positioned.fill(
            child: Container(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.bottomCenter,
                  end: Alignment.topCenter,
                  colors: [Colors.black, Colors.transparent],
                ),
              ),
            ),
          ),
          // Info Content
          SafeArea(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(24),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  IconButton(
                    icon: const Icon(Icons.arrow_back, color: Colors.white),
                    onPressed: () => context.pop(),
                  ),
                  const SizedBox(height: 100),
                  Text(
                    item.name,
                    style: const TextStyle(color: Colors.white, fontSize: 40, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 16),
                  Row(
                    children: [
                      if (item.rating != null) ...[
                        const Icon(Icons.star, color: Colors.yellow, size: 20),
                        const SizedBox(width: 4),
                        Text(item.rating!, style: const TextStyle(color: Colors.white, fontSize: 18)),
                        const SizedBox(width: 16),
                      ],
                      if (item.releaseDate != null)
                        Text(item.releaseDate!.split('-')[0], style: const TextStyle(color: Colors.grey, fontSize: 18)),
                      const SizedBox(width: 16),
                      if (item.duration != null)
                        Text(item.duration!, style: const TextStyle(color: Colors.grey, fontSize: 18)),
                    ],
                  ),
                  const SizedBox(height: 24),
                  SizedBox(
                    width: MediaQuery.of(context).size.width * 0.6,
                    child: Text(
                      item.description ?? 'No hay descripción disponible para este título.',
                      style: const TextStyle(color: Colors.white70, fontSize: 18, height: 1.5),
                    ),
                  ),
                  const SizedBox(height: 48),
                  
                  // ── BOTÓN REPRODUCIR ──────────────────────────────────
                  // Para: películas, series M3U directas (sin temporadas)
                  if (!isLoading &&
                      (fullContent ?? widget.content).streamUrl != null &&
                      (fullContent ?? widget.content).streamUrl!.isNotEmpty)
                    ElevatedButton.icon(
                      onPressed: () => context.push('/player', extra: fullContent ?? widget.content),
                      icon: const Icon(Icons.play_arrow, size: 30),
                      label: const Text('REPRODUCIR',
                          style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.red[700],
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(horizontal: 40, vertical: 16),
                        shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(8)),
                      ),
                    ),

                  // ── CARGANDO EPISODIOS ────────────────────────────────
                  if (isLoading)
                    const Padding(
                      padding: EdgeInsets.symmetric(vertical: 24),
                      child: Row(
                        children: [
                          CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                          SizedBox(width: 16),
                          Text('Cargando episodios...',
                              style: TextStyle(color: Colors.white70)),
                        ],
                      ),
                    ),

                  // ── TEMPORADAS ────────────────────────────────────────
                  if (!isLoading && item.seasons != null && item.seasons!.isNotEmpty)
                    _SeriesNavigator(seasons: item.seasons!),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _SeriesNavigator extends StatelessWidget {
  final List<SeasonModel> seasons;
  const _SeriesNavigator({required this.seasons});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: seasons.map<Widget>((season) {
        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 16),
              child: Text(
                season.name,
                style: const TextStyle(color: Colors.white, fontSize: 22, fontWeight: FontWeight.bold),
              ),
            ),
            SizedBox(
              height: 120,
              child: ListView.builder(
                scrollDirection: Axis.horizontal,
                itemCount: season.episodes.length,
                itemBuilder: (context, index) {
                  final ep = season.episodes[index];
                  return GestureDetector(
                    onTap: () {
                      // Crear un ChannelModel temporal para el reproductor
                      final channelEp = ChannelModel(
                        id: ep.id,
                        name: ep.name,
                        streamUrl: ep.streamUrl,
                      );
                      context.push('/player', extra: channelEp);
                    },
                    child: Container(
                      width: 200,
                      margin: const EdgeInsets.only(right: 12),
                      decoration: BoxDecoration(
                        color: Colors.grey[900],
                        borderRadius: BorderRadius.circular(8),
                      ),
                      padding: const EdgeInsets.all(12),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text(
                            'Episodio ${ep.episodeNumber}',
                            style: const TextStyle(color: Colors.grey),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            ep.name,
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                            textAlign: TextAlign.center,
                            style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
                          ),
                        ],
                      ),
                    ),
                  );
                },
              ),
            ),
          ],
        );
      }).toList(),
    );
  }
}
