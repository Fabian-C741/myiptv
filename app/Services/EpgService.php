<?php
namespace App\Services;

use App\Models\Channel;
use App\Models\EpgProgram;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class EpgService
{
    public function downloadAndParse($xmlUrl)
    {
        $response = Http::timeout(120)->get($xmlUrl);
        if (!$response->successful()) return false;
        
        // Very basic XMLTV parsing
        $xml = simplexml_load_string($response->body());
        if (!$xml) return false;

        foreach ($xml->programme as $prog) {
            $channelEpgId = (string)$prog['channel'];
            $start = Carbon::createFromFormat('YmdHis O', (string)$prog['start']);
            $end = Carbon::createFromFormat('YmdHis O', (string)$prog['stop']);
            $title = (string)$prog->title;
            $desc = (string)$prog->desc;

            // Find matching channels by epg_id
            $channels = Channel::where('epg_id', $channelEpgId)->get();
            
            foreach ($channels as $channel) {
                EpgProgram::updateOrCreate([
                    'channel_id' => $channel->id,
                    'title' => $title,
                    'start' => $start,
                ], [
                    'end' => $end,
                    'description' => $desc
                ]);
            }
        }
        return true;
    }
}
