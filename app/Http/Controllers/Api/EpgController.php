<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EpgProgram;
use Carbon\Carbon;

class EpgController extends Controller
{
    public function currentAndNext(Request $request, $channelId)
    {
        $now = Carbon::now();
        
        // Use Cache for performance
        $playing = \Illuminate\Support\Facades\Cache::remember("epg_{$channelId}", 60, function() use ($now, $channelId) {
            $current = EpgProgram::where('channel_id', $channelId)
                                 ->where('start', '<=', $now)
                                 ->where('end', '>', $now)
                                 ->first();

            $next = null;
            if ($current) {
                $next = EpgProgram::where('channel_id', $channelId)
                                  ->where('start', '>=', $current->end)
                                  ->orderBy('start', 'asc')
                                  ->first();
            }
            
            return ['current' => $current, 'next' => $next];
        });

        return response()->json($playing);
    }
}
