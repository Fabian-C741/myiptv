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
        $groups = [];
        $lineCount = count($lines);
        $foundChannels = false;
        
        for ($i = 0; $i < $lineCount; $i++) {
            $line = trim($lines[$i]);
            if (empty($line) || strpos($line, '#EXTINF:') !== 0) continue;

            $foundChannels = true;
            // ... (resto del código de parseo de #EXTINF)
            preg_match('/group-title="([^"]+)"/i', $line, $groupMatch);
            preg_match('/tvg-logo="([^"]+)"/i', $line, $logoMatch);
            $parts = explode(',', $line);
            $name = count($parts) > 1 ? trim(end($parts)) : "Canal Desconocido";
            
            $url = "";
            for ($j = $i + 1; $j < $lineCount; $j++) {
                $nextLine = trim($lines[$j]);
                if (empty($nextLine)) continue;
                if (strpos($nextLine, '#') === 0) {
                    if (strpos($nextLine, '#EXTINF') === 0) break;
                    continue; 
                }
                $url = $nextLine;
                $i = $j;
                break;
            }

            if (!empty($url)) {
                $groupName = $groupMatch[1] ?? 'General';
                $this->storeChannel($playlist, $url, $name, $groupName, $logoMatch[1] ?? null);
            }
        }

        // --- FALLBACK: Si no hay #EXTINF pero es un M3U8 válido (Master Manifest) ---
        if (!$foundChannels && (strpos($response->body(), '#EXTM3U') !== false)) {
            $this->storeChannel($playlist, $playlist->url, $playlist->name, 'Directo');
            return true;
        }

        return $foundChannels;
    }

    private function storeChannel($playlist, $url, $name, $groupName, $logo = null)
    {
        $group = ChannelGroup::updateOrCreate(
            ['playlist_id' => $playlist->id, 'name' => $groupName, 'type' => 'live'],
            ['is_adult' => false]
        );

        Channel::updateOrCreate(
            ['playlist_id' => $playlist->id, 'stream_url' => $url],
            [
                'channel_group_id' => $group->id,
                'type' => 'live',
                'name' => $name,
                'logo' => $logo,
                'is_active' => true
            ]
        );
    }
}
