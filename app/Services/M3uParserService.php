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
        // Petición con User-Agent para evitar bloqueos (Ej. GitHub)
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        ])->timeout(120)->get($playlist->url);

        if (!$response->successful()) { 
            \Illuminate\Support\Facades\Log::error("M3U Fetch Error: " . $response->status() . " - " . $playlist->url);
            return false; 
        }

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
        // Truncar para evitar errores SQL "Data too long" (límite por defecto de Laravel string = 255)
        $safeUrl = substr($url, 0, 250);
        $safeLogo = $logo ? substr($logo, 0, 250) : null;
        $safeName = substr($name, 0, 200);
        $safeGroupName = substr($groupName, 0, 100);

        try {
            $group = ChannelGroup::updateOrCreate(
                ['playlist_id' => $playlist->id, 'name' => $safeGroupName, 'type' => 'live'],
                ['is_adult' => false]
            );

            Channel::updateOrCreate(
                ['playlist_id' => $playlist->id, 'stream_url' => $safeUrl],
                [
                    'channel_group_id' => $group->id,
                    'type' => 'live',
                    'name' => $safeName,
                    'logo' => $safeLogo,
                    'is_adult' => false
                ]
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error guardando canal $safeName: " . $e->getMessage());
            // Si un canal falla, ignorar y continuar con el resto
        }
    }
}
