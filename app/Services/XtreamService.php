<?php

namespace App\Services;

use App\Models\Channel;
use App\Models\ChannelGroup;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XtreamService
{
    /**
     * Sincroniza canales desde una fuente Xtream Codes de forma profesional.
     */
    public function sync($source)
    {
        $baseUrl = rtrim($source->url, '/');
        $authParams = "username={$source->username}&password={$source->password}";
        $apiUrl = "{$baseUrl}/player_api.php?{$authParams}";

        try {
            // 1. Obtener información del servidor para validar conexión
            $serverInfo = Http::timeout(10)->get($apiUrl);
            if (!$serverInfo->successful()) {
                throw new \Exception("No se pudo conectar con el servidor IPTV.");
            }

            // 2. Sincronizar Categorías de forma eficiente
            $this->syncCategories($apiUrl, $source->id);

            // 3. Sincronizar Canales con URL de sintonización robusta
            $chanResponse = Http::timeout(30)->get("{$apiUrl}&action=get_live_streams");
            if ($chanResponse->successful()) {
                $channels = $chanResponse->json();
                foreach ($channels as $chan) {
                    $this->updateChannel($chan, $source, $baseUrl);
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Error en XtreamService Profesional: " . $e->getMessage());
            throw $e;
        }
    }

    private function syncCategories($apiUrl, $sourceId)
    {
        $response = Http::timeout(10)->get("{$apiUrl}&action=get_live_categories");
        if ($response->successful()) {
            foreach ($response->json() as $cat) {
                ChannelGroup::updateOrCreate(
                    ['external_id' => $cat['category_id'], 'playlist_id' => $sourceId],
                    ['name' => $cat['category_name'], 'type' => 'live']
                );
            }
        }
    }

    private function updateChannel($chan, $source, $baseUrl)
    {
        $groupId = ChannelGroup::where('external_id', $chan['category_id'])
                               ->where('playlist_id', $source->id)
                               ->value('id');

        // Formato Profesional: Detectar si el servidor prefiere .ts o .m3u8 (por defecto .ts)
        $extension = $chan['container_extension'] ?? 'ts';
        $streamUrl = "{$baseUrl}/live/{$source->username}/{$source->password}/{$chan['stream_id']}.{$extension}";

        Channel::updateOrCreate(
            ['stream_id' => $chan['stream_id'], 'playlist_id' => $source->id],
            [
                'name' => $chan['name'],
                'stream_url' => $streamUrl,
                'logo' => $chan['stream_icon'],
                'channel_group_id' => $groupId,
                'type' => 'live',
                'is_active' => true
            ]
        );
    }
}
