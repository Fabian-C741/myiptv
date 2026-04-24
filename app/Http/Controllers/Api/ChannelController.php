<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\ChannelGroup;
use Illuminate\Http\Request;

class ChannelController extends Controller
{
    public function index(Request $request)
    {
        $profile = $request->user()->profiles()->find($request->header('Profile-Id'));
        $isKid = $profile ? $profile->is_kid : false;
        $type = $request->query('type', 'live');

        $channels = Channel::where('type', $type)
            ->where('is_active', true)
            ->with(['group:id,name,is_adult'])
            ->when($isKid, fn ($q) => $q->where('is_adult', false))
            ->paginate(50);

        return response()->json($channels);
    }

    public function groups(Request $request)
    {
        $profile = $request->user()->profiles()->find($request->header('Profile-Id'));
        $isKid = $profile ? $profile->is_kid : false;
        $type = $request->query('type', 'live');

        $groups = ChannelGroup::where('type', $type)
            ->withCount('channels')
            ->when($isKid, fn ($q) => $q->where('is_adult', false))
            ->get(['id', 'name', 'type', 'is_adult', 'channels_count']);

        return response()->json($groups);
    }

    public function byGroup(Request $request, $groupId)
    {
        $profile = $request->user()->profiles()->find($request->header('Profile-Id'));
        $isKid = $profile ? $profile->is_kid : false;

        $channels = Channel::where('channel_group_id', $groupId)
            ->where('is_active', true)
            ->when($isKid, fn ($q) => $q->where('is_adult', false))
            ->paginate(50);

        return response()->json($channels);
    }

    public function seriesDetails($id)
    {
        $series = Channel::where('type', 'series')
            ->with(['seasons' => fn ($q) => $q->with('episodes')])
            ->findOrFail($id);

        return response()->json($series);
    }
}
