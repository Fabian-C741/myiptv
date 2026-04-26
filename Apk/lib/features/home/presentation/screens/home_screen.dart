import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:carousel_slider/carousel_slider.dart';
import '../providers/home_provider.dart';
import '../../../../core/theme/app_theme.dart';
import 'package:ott_app/shared/models/channel_model.dart';

// Filtro activo: 'all', 'series', 'movie', 'live'
final homeFilterProvider = StateProvider<String>((ref) => 'all');
// Categoría seleccionada en dropdown
final homeCategoryProvider = StateProvider<String?>((ref) => null);
// Índice del BottomNav
final homeNavIndexProvider = StateProvider<int>((ref) => 0);

class HomeScreen extends ConsumerWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final homeState = ref.watch(homeProvider);
    final navIndex = ref.watch(homeNavIndexProvider);

    return Scaffold(
      backgroundColor: Colors.black,
      extendBodyBehindAppBar: true,
      appBar: const _AppBar(),
      body: _buildBody(context, ref, homeState, navIndex),
      bottomNavigationBar: _BottomNav(
        currentIndex: navIndex,
        onTap: (i) {
          ref.read(homeNavIndexProvider.notifier).state = i;
          if (i == 1) context.push('/live-tv');
          if (i == 3) context.push('/profiles');
        },
      ),
    );
  }

  Widget _buildBody(BuildContext context, WidgetRef ref, HomeState homeState, int navIndex) {
    if (homeState.isLoading) {
      return const Center(child: CircularProgressIndicator(color: AppTheme.primaryRed));
    }
    if (navIndex == 2) {
      return _SearchView(channels: homeState.recentChannels + homeState.movies + homeState.series);
    }

    final filter = ref.watch(homeFilterProvider);
    final category = ref.watch(homeCategoryProvider);

    List<ChannelModel> displayedChannels = [];
    String gridTitle = '';

    if (category != null) {
      gridTitle = category;
      displayedChannels = (homeState.recentChannels + homeState.movies + homeState.series)
          .where((c) => c.groupId.toString() == category)
          .toList();
    } else if (filter == 'live') {
      gridTitle = 'TV en Vivo';
      displayedChannels = homeState.recentChannels;
    } else if (filter == 'movie') {
      gridTitle = 'Películas';
      displayedChannels = homeState.movies;
    } else if (filter == 'series') {
      gridTitle = 'Series';
      displayedChannels = homeState.series;
    }

    return RefreshIndicator(
      onRefresh: () => ref.read(homeProvider.notifier).initHome(),
      color: AppTheme.primaryRed,
      backgroundColor: Colors.black,
      child: CustomScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        slivers: [
          if (homeState.featuredChannels.isEmpty && 
              homeState.recentChannels.isEmpty && 
              homeState.movies.isEmpty && 
              homeState.series.isEmpty)
            SliverFillRemaining(
              child: Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(Icons.cloud_off, color: Colors.white24, size: 80),
                    const SizedBox(height: 16),
                    const Text('Aún no hay contenido disponible', style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 8),
                    const Text('Sincroniza tus fuentes en el panel admin.', style: TextStyle(color: Colors.white54)),
                    const SizedBox(height: 24),
                    ElevatedButton(
                      onPressed: () => ref.read(homeProvider.notifier).initHome(),
                      child: const Text('Reintentar'),
                    )
                  ],
                ),
              ),
            ),
          
          if (homeState.featuredChannels.isNotEmpty && filter == 'all' && category == null)
            SliverToBoxAdapter(
              child: _HeroBanner(channels: homeState.featuredChannels),
            ),

          if (filter == 'all' && category == null) ...[
            const SliverToBoxAdapter(child: SizedBox(height: 10)),
            ..._buildSliverGridSection(context, ref, 'TV en Vivo', homeState.recentChannels, limit: 18, showSeeAll: true, onSeeAll: () => ref.read(homeFilterProvider.notifier).state = 'live'),
            ..._buildSliverGridSection(context, ref, 'Películas', homeState.movies, limit: 18, showSeeAll: true, onSeeAll: () => ref.read(homeFilterProvider.notifier).state = 'movie'),
            ..._buildSliverGridSection(context, ref, 'Series', homeState.series, limit: 18, showSeeAll: true, onSeeAll: () => ref.read(homeFilterProvider.notifier).state = 'series'),
          ] else ...[
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.only(top: 100), // Espacio para el AppBar transparente
              ),
            ),
            ..._buildSliverGridSection(context, ref, gridTitle, displayedChannels, limit: null, showSeeAll: false, onSeeAll: null),
          ],
          
          const SliverToBoxAdapter(child: SizedBox(height: 100)),
        ],
      ),
    );
  }

  List<Widget> _buildSliverGridSection(BuildContext context, WidgetRef ref, String title, List<ChannelModel> channels, {int? limit, required bool showSeeAll, VoidCallback? onSeeAll}) {
    if (channels.isEmpty) return [];
    
    final displayList = limit != null && channels.length > limit ? channels.take(limit).toList() : channels;

    return [
      SliverToBoxAdapter(
        child: Padding(
          padding: const EdgeInsets.fromLTRB(16, 20, 16, 16),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(title, style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
              if (showSeeAll && channels.length > (limit ?? 0))
                GestureDetector(
                  onTap: onSeeAll,
                  child: const Text('Ver todos', style: TextStyle(color: AppTheme.primaryRed, fontSize: 14, fontWeight: FontWeight.w600)),
                ),
            ],
          ),
        ),
      ),
      SliverPadding(
        padding: const EdgeInsets.symmetric(horizontal: 16),
        sliver: SliverGrid(
          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 3,
            crossAxisSpacing: 10,
            mainAxisSpacing: 10,
            childAspectRatio: 1.4,
          ),
          delegate: SliverChildBuilderDelegate(
            (context, index) {
              final ch = displayList[index];
              return _GridItem(channel: ch);
            },
            childCount: displayList.length,
          ),
        ),
      ),
    ];
  }
}

// ── AppBar con logo propio + filtros funcionales ──────────────────────────────
class _AppBar extends ConsumerWidget implements PreferredSizeWidget {
  const _AppBar();

  @override
  Size get preferredSize => const Size.fromHeight(90);

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final homeState = ref.watch(homeProvider);
    final currentFilter = ref.watch(homeFilterProvider);

    return Container(
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topCenter,
          end: Alignment.bottomCenter,
          colors: [Colors.black.withOpacity(0.85), Colors.transparent],
        ),
      ),
      child: SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
          child: Row(
            children: [
              // LOGO PROPIO desde assets
              Image.asset(
                'assets/icons/logo.png',
                height: 36,
                errorBuilder: (_, __, ___) => const Text(
                  'ELECTROFABI',
                  style: TextStyle(
                    color: AppTheme.primaryRed,
                    fontWeight: FontWeight.w900,
                    fontSize: 18,
                    letterSpacing: 1,
                  ),
                ),
              ),
              const SizedBox(width: 10),
              // Botón Series
              _FilterChip(
                label: 'Series',
                isActive: currentFilter == 'series',
                onTap: () => ref.read(homeFilterProvider.notifier).state =
                    currentFilter == 'series' ? 'all' : 'series',
              ),
              const SizedBox(width: 4),
              // Botón Películas
              _FilterChip(
                label: 'Películas',
                isActive: currentFilter == 'movie',
                onTap: () => ref.read(homeFilterProvider.notifier).state =
                    currentFilter == 'movie' ? 'all' : 'movie',
              ),
              const SizedBox(width: 4),
              // Dropdown Categorías
              _CategoryDropdown(categories: homeState.categories),
              const Spacer(),
              // Búsqueda
              IconButton(
                icon: const Icon(Icons.search, color: Colors.white, size: 26),
                onPressed: () => ref.read(homeNavIndexProvider.notifier).state = 2,
              ),
              // Perfil
              IconButton(
                icon: const Icon(Icons.person_outline, color: Colors.white, size: 26),
                onPressed: () => context.push('/profiles'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _FilterChip extends StatelessWidget {
  final String label;
  final bool isActive;
  final VoidCallback onTap;
  const _FilterChip({required this.label, required this.isActive, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
        decoration: BoxDecoration(
          color: isActive ? Colors.white : Colors.transparent,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: isActive ? Colors.white : Colors.white54),
        ),
        child: Text(
          label,
          style: TextStyle(
            color: isActive ? Colors.black : Colors.white,
            fontSize: 12,
            fontWeight: FontWeight.w600,
          ),
        ),
      ),
    );
  }
}

class _CategoryDropdown extends ConsumerWidget {
  final List<CategoryModel> categories;
  const _CategoryDropdown({required this.categories});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final selected = ref.watch(homeCategoryProvider);
    return GestureDetector(
      onTap: () => _showCategorySheet(context, ref),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
        decoration: BoxDecoration(
          color: selected != null ? Colors.white : Colors.transparent,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: selected != null ? Colors.white : Colors.white54),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              selected != null ? (categories.firstWhere((c) => c.id == selected, orElse: () => CategoryModel(id: selected, name: selected, type: '')).name) : 'Categorías',
              style: TextStyle(
                color: selected != null ? Colors.black : Colors.white,
                fontSize: 12,
                fontWeight: FontWeight.w600,
              ),
            ),
            Icon(Icons.arrow_drop_down,
                color: selected != null ? Colors.black : Colors.white, size: 16),
          ],
        ),
      ),
    );
  }

  void _showCategorySheet(BuildContext context, WidgetRef ref) {
    showModalBottomSheet(
      context: context,
      backgroundColor: const Color(0xFF1a1a1e),
      shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (_) => Consumer(builder: (ctx, r, __) {
        final sel = r.watch(homeCategoryProvider);
        return Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const SizedBox(height: 12),
            Container(width: 40, height: 4, decoration: BoxDecoration(color: Colors.white24, borderRadius: BorderRadius.circular(2))),
            const SizedBox(height: 16),
            const Text('Categorías', style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
            const Divider(color: Colors.white12),
            ListTile(
              leading: Icon(Icons.all_inclusive, color: sel == null ? AppTheme.primaryRed : Colors.white54),
              title: Text('Todas', style: TextStyle(color: sel == null ? AppTheme.primaryRed : Colors.white)),
              trailing: sel == null ? const Icon(Icons.check, color: AppTheme.primaryRed) : null,
              onTap: () {
                r.read(homeCategoryProvider.notifier).state = null;
                r.read(homeFilterProvider.notifier).state = 'all';
                Navigator.pop(ctx);
              },
            ),
            Flexible(
              child: ListView.builder(
                shrinkWrap: true,
                itemCount: categories.length,
                itemBuilder: (_, i) {
                  final cat = categories[i];
                  final isSelected = sel == cat.id;
                  return ListTile(
                    leading: Icon(Icons.tv, color: isSelected ? AppTheme.primaryRed : Colors.white54),
                    title: Text(cat.name, style: TextStyle(color: isSelected ? AppTheme.primaryRed : Colors.white)),
                    trailing: isSelected ? const Icon(Icons.check, color: AppTheme.primaryRed) : null,
                    onTap: () {
                      r.read(homeCategoryProvider.notifier).state = cat.id;
                      r.read(homeFilterProvider.notifier).state = 'all';
                      Navigator.pop(ctx);
                    },
                  );
                },
              ),
            ),
            const SizedBox(height: 20),
          ],
        );
      }),
    );
  }
}

// ── BottomNav ─────────────────────────────────────────────────────────────────
class _BottomNav extends StatelessWidget {
  final int currentIndex;
  final ValueChanged<int> onTap;
  const _BottomNav({required this.currentIndex, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return BottomNavigationBar(
      currentIndex: currentIndex,
      onTap: onTap,
      backgroundColor: Colors.black.withOpacity(0.95),
      unselectedItemColor: Colors.grey,
      selectedItemColor: Colors.white,
      type: BottomNavigationBarType.fixed,
      selectedLabelStyle: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600),
      unselectedLabelStyle: const TextStyle(fontSize: 11),
      items: const [
        BottomNavigationBarItem(
          icon: Icon(Icons.home_outlined),
          activeIcon: Icon(Icons.home),
          label: 'Inicio',
        ),
        BottomNavigationBarItem(
          icon: Icon(Icons.live_tv_outlined),
          activeIcon: Icon(Icons.live_tv),
          label: 'TV en Vivo',
        ),
        BottomNavigationBarItem(
          icon: Icon(Icons.search_outlined),
          activeIcon: Icon(Icons.search),
          label: 'Buscar',
        ),
        BottomNavigationBarItem(
          icon: Icon(Icons.person_outline),
          activeIcon: Icon(Icons.person),
          label: 'Mi Perfil',
        ),
      ],
    );
  }
}

// ── Hero Banner ───────────────────────────────────────────────────────────────
class _HeroBanner extends StatelessWidget {
  final List<ChannelModel> channels;
  const _HeroBanner({required this.channels});

  @override
  Widget build(BuildContext context) {
    return CarouselSlider(
      options: CarouselOptions(
        height: MediaQuery.of(context).size.height * 0.65,
        viewportFraction: 1.0,
        autoPlay: true,
        autoPlayInterval: const Duration(seconds: 8),
      ),
      items: channels.map((ch) {
        return Stack(
          fit: StackFit.expand,
          children: [
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 12),
              child: ClipRRect(
                borderRadius: BorderRadius.circular(14),
                child: Image.network(
                  ch.backdrop ?? ch.logo ?? '',
                  fit: BoxFit.cover,
                  errorBuilder: (_, __, ___) => Container(color: Colors.grey[900]),
                ),
              ),
            ),
            Container(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.bottomCenter,
                  end: Alignment.topCenter,
                  stops: const [0.0, 0.35, 0.7],
                  colors: [Colors.black, Colors.black.withOpacity(0.6), Colors.transparent],
                ),
              ),
            ),
            Positioned(
              bottom: 50,
              left: 24,
              right: 24,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    ch.displayName.toUpperCase(),
                    style: const TextStyle(color: Colors.white, fontSize: 30, fontWeight: FontWeight.w900, letterSpacing: -0.5),
                    maxLines: 2,
                  ),
                  if (ch.description != null) ...[
                    const SizedBox(height: 6),
                    Text(ch.description!, maxLines: 2, style: const TextStyle(color: Colors.white70, fontSize: 13)),
                  ],
                  const SizedBox(height: 16),
                  Row(
                    children: [
                      ElevatedButton.icon(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.white,
                          foregroundColor: Colors.black,
                          minimumSize: const Size(130, 42),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(4)),
                        ),
                        onPressed: () => context.push('/player', extra: ch),
                        icon: const Icon(Icons.play_arrow, size: 22),
                        label: const Text('Reproducir', style: TextStyle(fontWeight: FontWeight.bold)),
                      ),
                      const SizedBox(width: 10),
                      ElevatedButton.icon(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.white24,
                          foregroundColor: Colors.white,
                          minimumSize: const Size(120, 42),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(4)),
                        ),
                        onPressed: () => context.push('/details', extra: ch),
                        icon: const Icon(Icons.add, size: 22),
                        label: const Text('Mi lista', style: TextStyle(fontWeight: FontWeight.bold)),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        );
      }).toList(),
    );
  }
}

// ── Elemento Individual de Cuadrícula ─────────────────────────────────────────
class _GridItem extends StatelessWidget {
  final ChannelModel channel;
  const _GridItem({required this.channel});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () => context.push('/player', extra: channel),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(8),
        child: Container(
          decoration: BoxDecoration(
            color: Colors.grey[900],
            image: DecorationImage(
              image: NetworkImage(channel.logo ?? 'https://via.placeholder.com/220x124'),
              fit: BoxFit.cover,
            ),
          ),
          alignment: Alignment.bottomLeft,
          child: Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 4),
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.bottomCenter,
                end: Alignment.topCenter,
                colors: [Colors.black87, Colors.black45, Colors.transparent],
              ),
            ),
            child: Text(
              channel.displayName,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
            ),
          ),
        ),
      ),
    );
  }
}

// ── Vista de búsqueda ─────────────────────────────────────────────────────────
class _SearchView extends StatefulWidget {
  final List<ChannelModel> channels;
  const _SearchView({required this.channels});

  @override
  State<_SearchView> createState() => _SearchViewState();
}

class _SearchViewState extends State<_SearchView> {
  String _query = '';

  @override
  Widget build(BuildContext context) {
    final results = _query.isEmpty
        ? widget.channels
        : widget.channels.where((c) => c.displayName.toLowerCase().contains(_query.toLowerCase())).toList();

    return Column(
      children: [
        const SizedBox(height: 80),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16),
          child: TextField(
            autofocus: true,
            style: const TextStyle(color: Colors.white),
            decoration: InputDecoration(
              hintText: 'Buscar canales, películas...',
              hintStyle: const TextStyle(color: Colors.white38),
              prefixIcon: const Icon(Icons.search, color: Colors.white38),
              filled: true,
              fillColor: Colors.white12,
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
            ),
            onChanged: (v) => setState(() => _query = v),
          ),
        ),
        const SizedBox(height: 16),
        Expanded(
          child: GridView.builder(
            padding: const EdgeInsets.all(16),
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 3,
              crossAxisSpacing: 8,
              mainAxisSpacing: 8,
              childAspectRatio: 1.6,
            ),
            itemCount: results.length,
            itemBuilder: (_, i) {
              final ch = results[i];
              return GestureDetector(
                onTap: () => context.push('/player', extra: ch),
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(6),
                  child: Image.network(ch.logo ?? '', fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => Container(color: Colors.grey[850],
                          child: Center(child: Text(ch.displayName[0], style: const TextStyle(color: Colors.white, fontSize: 24))))),
                ),
              );
            },
          ),
        ),
      ],
    );
  }
}
