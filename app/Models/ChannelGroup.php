<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChannelGroup extends Model
{
    protected $fillable = ['name', 'slug', 'type', 'external_id', 'source_id'];

    public function channels()
    {
        return $this->hasMany(Channel::class, 'channel_group_id');
    }
}
