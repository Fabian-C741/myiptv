<?php

namespace Tests\Unit;

use App\Models\Channel;
use App\Models\ChannelGroup;
use App\Models\Device;
use App\Models\Playlist;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_have_profiles(): void
    {
        $user = User::factory()->create();
        $profile = Profile::create([
            'user_id' => $user->id,
            'name' => 'Test Profile',
            'is_kid' => false,
        ]);

        $this->assertInstanceOf(Profile::class, $user->profiles->first());
    }

    public function test_user_can_have_devices(): void
    {
        $user = User::factory()->create();
        $device = Device::create([
            'user_id' => $user->id,
            'device_id' => 'test-device-001',
            'device_name' => 'Test Device',
            'is_active' => true,
        ]);

        $this->assertInstanceOf(Device::class, $user->devices->first());
    }

    public function test_channel_belongs_to_group(): void
    {
        $group = ChannelGroup::create(['name' => 'Test Group', 'type' => 'live']);
        $channel = Channel::create([
            'name' => 'Test Channel',
            'type' => 'live',
            'channel_group_id' => $group->id,
        ]);

        $this->assertInstanceOf(ChannelGroup::class, $channel->group);
    }

    public function test_channel_belongs_to_playlist(): void
    {
        $playlist = Playlist::create(['name' => 'Test Playlist', 'type' => 'm3u']);
        $channel = Channel::create([
            'name' => 'Test Channel',
            'type' => 'live',
            'playlist_id' => $playlist->id,
        ]);

        $this->assertInstanceOf(Playlist::class, $channel->playlist);
    }

    public function test_channel_is_active_filter(): void
    {
        $group = ChannelGroup::create(['name' => 'Test Group', 'type' => 'live']);
        $active = Channel::create(['name' => 'Active', 'type' => 'live', 'channel_group_id' => $group->id, 'is_active' => true]);
        $inactive = Channel::create(['name' => 'Inactive', 'type' => 'live', 'channel_group_id' => $group->id, 'is_active' => false]);

        $this->assertTrue(Channel::where('is_active', true)->exists());
        $this->assertFalse(Channel::where('is_active', false)->where('name', 'Active')->exists());
    }
}
