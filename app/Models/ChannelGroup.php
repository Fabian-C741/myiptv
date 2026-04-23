<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChannelGroup extends Model
{
    protected $fillable = ['name', 'slug', 'type', 'external_id', 'source_id', 'playlist_id', 'is_adult'];

    public function channels()
    {
        return $this->hasMany(Channel::class, 'channel_group_id');
    }
}
