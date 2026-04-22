<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    protected $fillable = ['profile_id', 'channel_id'];

    public function profile() { return $this->belongsTo(Profile::class); }
    public function channel() { return $this->belongsTo(Channel::class); }
}
