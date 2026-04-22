<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    protected $fillable = [
        'playlist_id', 
        'channel_group_id', 
        'name', 
        'type', // live, movie, series
        'stream_url', 
        'stream_id', 
        'logo', 
        'epg_id', 
        'is_adult',
        'description',
        'release_date',
        'rating',
        'duration',
        'backdrop',
        'tmdb_id'
    ];
    protected $casts = ['is_adult' => 'boolean'];

    public function playlist() { return $this->belongsTo(Playlist::class); }
    public function group() { return $this->belongsTo(ChannelGroup::class, 'channel_group_id'); }
    public function epgPrograms() { return $this->hasMany(EpgProgram::class); }
    public function seasons() { return $this->hasMany(Season::class, 'channel_id'); }
}
