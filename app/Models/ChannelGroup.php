<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChannelGroup extends Model
{
    protected $fillable = ['playlist_id', 'name', 'type', 'ext_id', 'is_adult'];
    protected $casts = ['is_adult' => 'boolean'];

    public function playlist() { return $this->belongsTo(Playlist::class); }
    public function channels() { return $this->hasMany(Channel::class); }
}
