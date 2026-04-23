<?php

namespace App\Services;

use App\Models\Channel;
use App\Models\ChannelGroup;
use App\Models\ExternalSource;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XtreamService
{
    /**
     * Sincroniza canales, películas y series desde una fuente Xtream Codes.
     */
    public function sync(ExternalSource $source)
    {
        $baseUrl = rtrim($source->url, '/');
        $authUrl = "{$baseUrl}/player_api.php?username={$source->username}&password={$source->password}";

        try {
            // 1. Obtener Categorías (Live)
            $catResponse = Http::get("{$authUrl}&action=get_live_categories");
            if ($catResponse->successful()) {
                $categories = $catResponse->json();
                foreach ($categories as $cat) {
                    ChannelGroup::updateOrCreate(
                        ['external_id' => $cat['category_id'], 'source_id' => $source->id],
                        ['name' => $cat['category_name'], 'type' => 'live']
                    );
                }
            }

            // 2. Obtener Canales (Live)
            $chanResponse = Http::get("{$authUrl}&action=get_live_streams");
            if ($chanResponse->successful()) {
                $channels = $chanResponse->json();
                foreach ($channels as $chan) {
                    $group = ChannelGroup::where('external_id', $chan['category_id'])->first();
                    
                    Channel::updateOrCreate(
                        ['stream_id' => $chan['stream_id'], 'source_id' => $source->id],
                        [
                            'name' => $chan['name'],
                            'stream_url' => "{$baseUrl}/{$chan['stream_id']}.m3u8", // Formato típico
                            'logo' => $chan['stream_icon'],
                            'channel_group_id' => $group?->id,
                            'type' => 'live',
                            'is_active' => true
                        ]
                    );
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Error sincronizando Xtream: " . $e->getMessage());
            return false;
        }
    }
}
