<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\ChannelGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LegalChannelController extends Controller
{
    public function sync()
    {
        try {
            // URL de canales legales verificados por la comunidad
            $url = "https://iptv-org.github.io/iptv/index.m3u";
            $response = Http::get($url);
            
            if ($response->successful()) {
                $lines = explode("\n", $response->body());
                $count = 0;
                
                // Aseguramos que exista un grupo para canales legales
                $group = ChannelGroup::firstOrCreate([
                    'name' => 'Canales Legales (IPTV-org)',
                    'slug' => 'canales-legales'
                ]);

                for ($i = 0; $i < count($lines); $i++) {
                    if (str_starts_with($lines[$i], '#EXTINF')) {
                        // Extraemos datos básicos
                        preg_match('/tvg-logo="([^"]*)"/', $lines[$i], $logo);
                        preg_match('/,(.*)/', $lines[$i], $name);
                        
                        $channelName = trim($name[1] ?? 'Canal Desconocido');
                        $streamUrl = trim($lines[$i + 1] ?? '');

                        if ($streamUrl && !Channel::where('stream_url', $streamUrl)->exists()) {
                            Channel::create([
                                'name' => $channelName,
                                'stream_url' => $streamUrl,
                                'logo' => $logo[1] ?? null,
                                'channel_group_id' => $group->id,
                                'is_active' => true
                            ]);
                            $count++;
                        }
                        
                        // Limitamos a 50 por vez para no saturar el servidor
                        if ($count >= 50) break;
                    }
                }
                
                return back()->with('success', "Se han sincronizado $count nuevos canales legales.");
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Error al sincronizar: ' . $e->getMessage());
        }

        return back()->with('error', 'No se pudo obtener la lista de canales.');
    }
}
