<?php
namespace App\Services;

use App\Models\Playlist;
use App\Models\ChannelGroup;
use App\Models\Channel;
use App\Models\Season;
use App\Models\Episode;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\TmdbService;

class XtreamService 
{
    protected $tmdb;

    public function __construct(TmdbService $tmdb)
    {
        $this->tmdb = $tmdb;
    }

    public function sync(Playlist $playlist)
    {
        $baseUrl = rtrim($playlist->url, '/');
        $authParams = [
            'username' => $playlist->username,
            'password' => $playlist->password
        ];

        Log::info("Starting Xtream Sync for Playlist ID: {$playlist->id}");
        
        // --- LIMPIEZA PREVIA ---
        // Borramos los canales y grupos antiguos para que la nueva carga sea 100% limpia y filtrada
        $playlist->channels()->delete();
        $playlist->channelGroups()->delete();

        // 1. Sync Categories for all types
        $this->syncCategories($playlist, $baseUrl, $authParams, 'get_live_categories', 'live');
        $this->syncCategories($playlist, $baseUrl, $authParams, 'get_vod_categories', 'movie');
        $this->syncCategories($playlist, $baseUrl, $authParams, 'get_series_categories', 'series');

        // 2. Sync Streams
        $this->syncLive($playlist, $baseUrl, $authParams);
        $this->syncVod($playlist, $baseUrl, $authParams);
        $this->syncSeries($playlist, $baseUrl, $authParams);

        return true;
    }

    protected function syncCategories($playlist, $baseUrl, $params, $action, $type)
    {
        $response = Http::timeout(30)->get($baseUrl . '/player_api.php', array_merge($params, ['action' => $action]));
        if ($response->successful()) {
            foreach ($response->json() as $cat) {
                $isAdult = (strpos(strtolower($cat['category_name']), 'xxx') !== false || strpos(strtolower($cat['category_name']), 'adult') !== false);
                ChannelGroup::updateOrCreate([
                    'playlist_id' => $playlist->id,
                    'ext_id' => $cat['category_id'],
                    'type' => $type
                ], [
                    'name' => $cat['category_name'],
                    'is_adult' => $isAdult
                ]);
            }
        }
    }

    protected function syncLive($playlist, $baseUrl, $params)
    {
        $response = Http::timeout(60)->get($baseUrl . '/player_api.php', array_merge($params, ['action' => 'get_live_streams']));
        if ($response->successful()) {
            foreach ($response->json() as $stream) {
                $group = ChannelGroup::where('playlist_id', $playlist->id)->where('ext_id', $stream['category_id'])->where('type', 'live')->first();
                if (!$group) continue;
                
                // Filtro: Si el nombre tiene muchos puntos y coma, es una cabecera de categoría, no un canal real
                if (strpos($stream['name'], ';') !== false && substr_count($stream['name'], ';') > 1) {
                    continue;
                }

                $url = $baseUrl . '/live/' . $playlist->username . '/' . $playlist->password . '/' . $stream['stream_id'] . '.m3u8';
                
                Channel::updateOrCreate([
                    'playlist_id' => $playlist->id,
                    'stream_id' => $stream['stream_id'],
                    'type' => 'live'
                ], [
                    'channel_group_id' => $group->id,
                    'name' => $stream['name'],
                    'stream_url' => $url,
                    'logo' => $stream['stream_icon'] ?? null,
                    'is_adult' => $group->is_adult
                ]);
            }
        }
    }

    protected function syncVod($playlist, $baseUrl, $params)
    {
        $response = Http::timeout(120)->get($baseUrl . '/player_api.php', array_merge($params, ['action' => 'get_vod_streams']));
        if ($response->successful()) {
            foreach ($response->json() as $stream) {
                $group = ChannelGroup::where('playlist_id', $playlist->id)->where('ext_id', $stream['category_id'])->where('type', 'movie')->first();
                if (!$group) continue;

                // Filtro para VOD
                if (strpos($stream['name'], ';') !== false && substr_count($stream['name'], ';') > 1) {
                    continue;
                }

                $url = $baseUrl . '/movie/' . $playlist->username . '/' . $playlist->password . '/' . $stream['stream_id'] . '.' . ($stream['container_extension'] ?? 'mp4');
                
                $movie = Channel::updateOrCreate([
                    'playlist_id' => $playlist->id,
                    'stream_id' => $stream['stream_id'],
                    'type' => 'movie'
                ], [
                    'channel_group_id' => $group->id,
                    'name' => $stream['name'],
                    'stream_url' => $url,
                    'logo' => $stream['stream_icon'] ?? null,
                    'is_adult' => $group->is_adult,
                    'rating' => $stream['rating'] ?? null,
                ]);

                // Enrichment with TMDB (Optional background job recommended in real scenario)
                if (!$movie->tmdb_id) {
                    $metadata = $this->tmdb->search($movie->name, 'movie');
                    if ($metadata) {
                        $movie->update([
                            'tmdb_id' => $metadata['id'],
                            'description' => $metadata['overview'] ?? null,
                            'release_date' => $metadata['release_date'] ?? null,
                            'backdrop' => $metadata['backdrop_path'] ? 'https://image.tmdb.org/t/p/original' . $metadata['backdrop_path'] : null,
                            'rating' => $movie->rating ?? $metadata['vote_average'],
                        ]);
                    }
                }
            }
        }
    }

    protected function syncSeries($playlist, $baseUrl, $params)
    {
        $response = Http::timeout(120)->get($baseUrl . '/player_api.php', array_merge($params, ['action' => 'get_series']));
        if ($response->successful()) {
            foreach ($response->json() as $entry) {
                $group = ChannelGroup::where('playlist_id', $playlist->id)->where('ext_id', $entry['category_id'])->where('type', 'series')->first();
                if (!$group) continue;

                $series = Channel::updateOrCreate([
                    'playlist_id' => $playlist->id,
                    'stream_id' => $entry['series_id'],
                    'type' => 'series'
                ], [
                    'channel_group_id' => $group->id,
                    'name' => $entry['name'],
                    'logo' => $entry['cover'] ?? null,
                    'is_adult' => $group->is_adult,
                    'description' => $entry['plot'] ?? null,
                    'release_date' => $entry['releaseDate'] ?? null,
                    'rating' => $entry['rating'] ?? null,
                ]);

                // enrichment TMDB series
                if (!$series->tmdb_id) {
                    $metadata = $this->tmdb->search($series->name, 'series');
                    if ($metadata) {
                        $series->update(['tmdb_id' => $metadata['id']]);
                    }
                }

                // Sync Episodes
                $this->syncSeriesInfo($playlist, $baseUrl, $params, $series, $entry['series_id']);
            }
        }
    }

    protected function syncSeriesInfo($playlist, $baseUrl, $params, $series, $seriesExtId)
    {
        $response = Http::timeout(60)->get($baseUrl . '/player_api.php', array_merge($params, [
            'action' => 'get_series_info',
            'series_id' => $seriesExtId
        ]));

        if ($response->successful()) {
            $data = $response->json();
            $episodesData = $data['episodes'] ?? [];

            foreach ($episodesData as $seasonNum => $episodesList) {
                $season = Season::updateOrCreate([
                    'channel_id' => $series->id,
                    'season_number' => $seasonNum
                ], [
                    'name' => "Temporada " . $seasonNum
                ]);

                foreach ($episodesList as $ep) {
                    $url = $baseUrl . '/series/' . $playlist->username . '/' . $playlist->password . '/' . $ep['id'] . '.' . ($ep['container_extension'] ?? 'mp4');
                    Episode::updateOrCreate([
                        'season_id' => $season->id,
                        'episode_number' => $ep['episode_num'],
                        'stream_id' => $ep['id']
                    ], [
                        'name' => $ep['title'] ?? ('Episodio ' . $ep['episode_num']),
                        'stream_url' => $url,
                        'duration' => $ep['info']['duration'] ?? null,
                    ]);
                }
            }
        }
    }
}
