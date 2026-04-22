<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    protected $fillable = ['channel_id', 'name', 'season_number', 'tmdb_id'];

    public function series()
    {
        return $this->belongsTo(Channel::class, 'channel_id');
    }

    public function episodes()
    {
        return $this->hasMany(Episode::class);
    }
}
