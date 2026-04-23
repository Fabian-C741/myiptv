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
        
        for ($i = 0; $i < $lineCount; $i++) {
            $line = trim($lines[$i]);
            if (empty($line) || strpos($line, '#EXTINF:') !== 0) continue;

            // --- PARSEAR CABECERA #EXTINF ---
            preg_match('/group-title="([^"]+)"/i', $line, $groupMatch);
            preg_match('/tvg-logo="([^"]+)"/i', $line, $logoMatch);
            
            // Nombre: Todo lo que esté después de la última coma
            $parts = explode(',', $line);
            $name = count($parts) > 1 ? trim(end($parts)) : "Canal Desconocido";
            
            // --- BUSCAR LA URL (Puede no estar en la línea inmediatamente siguiente) ---
            $url = "";
            for ($j = $i + 1; $j < $lineCount; $j++) {
                $nextLine = trim($lines[$j]);
                if (empty($nextLine)) continue;
                if (strpos($nextLine, '#') === 0) {
                    // Si encontramos otra etiqueta antes de la URL, algo está mal o es una etiqueta extra
                    if (strpos($nextLine, '#EXTINF') === 0) break; // Siguiente canal, abortar búsqueda
                    continue; 
                }
                $url = $nextLine;
                $i = $j; // Saltamos a esta línea para el siguiente ciclo del loop principal
                break;
            }

            if (!empty($url)) {
                $groupName = $groupMatch[1] ?? 'General';
                $logo = $logoMatch[1] ?? null;
                
                // Categorización Simple
                $type = 'live';
                $lowerGroup = strtolower($groupName);
                if (strpos($lowerGroup, 'movie') !== false || strpos($lowerGroup, 'peli') !== false) $type = 'movie';
                if (strpos($lowerGroup, 'series') !== false || strpos($lowerGroup, 'episodio') !== false) $type = 'series';

                if (!isset($groups[$groupName])) {
                    $groupModel = ChannelGroup::updateOrCreate(
                        ['playlist_id' => $playlist->id, 'name' => $groupName, 'type' => $type],
                        ['is_adult' => false]
                    );
                    $groups[$groupName] = $groupModel->id;
                }

                Channel::updateOrCreate(
                    ['playlist_id' => $playlist->id, 'stream_url' => $url],
                    [
                        'channel_group_id' => $groups[$groupName],
                        'type' => $type,
                        'name' => $name,
                        'logo' => $logo,
                        'is_active' => true
                    ]
                );
            }
        }
        return true;
    }
}
