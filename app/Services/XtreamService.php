<?php

namespace App\Services;

use App\Models\Channel;
use App\Models\ChannelGroup;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XtreamService
{
    /**
     * Sincroniza canales desde una fuente Xtream Codes de forma simple y funcional.
     */
    public function sync($source)
    {
        $baseUrl = rtrim($source->url, '/');
        $apiUrl = "{$baseUrl}/player_api.php?username={$source->username}&password={$source->password}";

        try {
            // 1. Categorías
            $catResponse = Http::timeout(10)->get("{$apiUrl}&action=get_live_categories");
            if ($catResponse->successful()) {
                foreach ($catResponse->json() as $cat) {
                    ChannelGroup::updateOrCreate(
                        ['external_id' => $cat['category_id'], 'playlist_id' => $source->id],
                        ['name' => $cat['category_name'], 'type' => 'live']
                    );
                }
            }

            // 2. Canales
            $chanResponse = Http::timeout(30)->get("{$apiUrl}&action=get_live_streams");
            if ($chanResponse->successful()) {
                foreach ($chanResponse->json() as $chan) {
                    $groupId = ChannelGroup::where('external_id', $chan['category_id'])
                                           ->where('playlist_id', $source->id)
                                           ->value('id');

                    // Formato más básico: http://dominio/usuario/password/id
                    $streamUrl = "{$baseUrl}/{$source->username}/{$source->password}/{$chan['stream_id']}";

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

            return true;
        } catch (\Exception $e) {
            Log::error("Error en XtreamService: " . $e->getMessage());
            throw $e;
        }
    }
}
