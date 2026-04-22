<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChannelGroup;
use App\Models\Channel;

class ChannelController extends Controller
{
    public function index(Request $request)
    {
        $profile = $request->user()->profiles()->find($request->header('Profile-Id'));
        $isKid = $profile ? $profile->is_kid : false;
        $type = $request->query('type', 'live');

        $channels = Channel::where('type', $type)
            ->when($isKid, function($query) {
                return $query->where('is_adult', false);
            })
            ->paginate(50);

        return response()->json($channels);
    }

    public function groups(Request $request)
    {
        $profile = $request->user()->profiles()->find($request->header('Profile-Id'));
        $isKid = $profile ? $profile->is_kid : false;
        $type = $request->query('type', 'live');

        $groups = ChannelGroup::where('type', $type)
            ->when($isKid, function($query) {
                return $query->where('is_adult', false);
            })->get();

        return response()->json($groups);
    }

    public function byGroup(Request $request, $groupId)
    {
        $profile = $request->user()->profiles()->find($request->header('Profile-Id'));
        $isKid = $profile ? $profile->is_kid : false;

        $channels = Channel::where('channel_group_id', $groupId)
            ->when($isKid, function($query) {
                return $query->where('is_adult', false);
            })
            ->paginate(50);

        return response()->json($channels);
    }

    /**
     * Get Series structure: Seasons -> Episodes
     */
    public function seriesDetails($id)
    {
        $series = Channel::where('type', 'series')
            ->with(['seasons.episodes'])
            ->findOrFail($id);
            
        return response()->json($series);
    }
}
