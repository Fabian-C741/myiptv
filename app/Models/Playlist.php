<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    protected $fillable = ['name', 'url', 'type', 'username', 'password'];
    protected $hidden = ['password'];

    public function channelGroups() { return $this->hasMany(ChannelGroup::class); }
    public function channels() { return $this->hasMany(Channel::class); }
}
