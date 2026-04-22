<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Episode extends Model
{
    protected $fillable = ['season_id', 'name', 'episode_number', 'stream_url', 'stream_id', 'tmdb_id', 'duration'];

    public function season()
    {
        return $this->belongsTo(Season::class);
    }
}
