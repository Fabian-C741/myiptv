<?php
namespace App\Services;

use App\Models\Playlist;
use App\Models\ChannelGroup;
use App\Models\Channel;
use Illuminate\Support\Facades\Http;

class M3uParserService
{
    public function parseAndStore(Playlist $playlist)
    {
        $response = Http::timeout(120)->get($playlist->url);
        if (!$response->successful()) { return false; }

        // --- LIMPIEZA PREVIA ---
        $playlist->channels()->delete();
        $playlist->channelGroups()->delete();

        $lines = explode("\n", $response->body());
        $groups = []; // cache array
        
        foreach ($lines as $index => $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (strpos($line, '#EXTINF:') === 0) {
                // Parse attributes
                preg_match('/group-title="([^"]+)"/i', $line, $groupMatch);
                preg_match('/tvg-logo="([^"]+)"/i', $line, $logoMatch);
                
                // Get Name
                $parts = explode(',', $line);
                $name = trim(end($parts));
                
                // Filtro Anti-Basura: Si el nombre tiene muchos puntos y coma o es un separador
                if (strpos($name, ';') !== false && substr_count($name, ';') > 1) {
                    continue;
                }
                
                $groupName = $groupMatch[1] ?? 'Uncategorized';
                $logo = $logoMatch[1] ?? null;
                
                // Detect Type
                $type = 'live';
                $lowerGroup = strtolower($groupName);
                $lowerName = strtolower($name);

                if (strpos($lowerGroup, 'movie') !== false || strpos($lowerGroup, 'peli') !== false || strpos($lowerGroup, 'cinema') !== false || strpos($lowerGroup, 'vod') !== false) {
                    $type = 'movie';
                } elseif (strpos($lowerGroup, 'series') !== false || strpos($lowerGroup, 'tv show') !== false || preg_match('/s\d{1,2}e\d{1,2}/i', $name)) {
                    $type = 'series';
                }

                $isAdult = (strpos($lowerGroup, 'adult') !== false || strpos($lowerName, 'adult') !== false || strpos($lowerGroup, 'xxx') !== false);
                
                // Get URL on next line
                $url = trim($lines[$index + 1] ?? '');
                
                if (!empty($url) && strpos($url, '#') !== 0) {
                    if (!isset($groups[$groupName])) {
                        $groupModel = ChannelGroup::updateOrCreate([
                            'playlist_id' => $playlist->id,
                            'name' => $groupName,
                            'type' => $type
                        ], ['is_adult' => $isAdult]);
                        $groups[$groupName] = $groupModel->id;
                    }

                    // For M3U, treats series episodes as single movies usually, 
                    // unless we implement complex parsing. For now, categorize them.
                    Channel::updateOrCreate([
                        'playlist_id' => $playlist->id,
                        'stream_url' => $url,
                    ], [
                        'channel_group_id' => $groups[$groupName],
                        'type' => $type,
                        'name' => $name,
                        'logo' => $logo,
                        'is_adult' => $isAdult
                    ]);
                }
            }
        }
        return true;
    }
}
