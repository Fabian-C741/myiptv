<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EpgProgram extends Model
{
    protected $fillable = ['channel_id', 'title', 'description', 'start', 'end'];
    protected $casts = ['start' => 'datetime', 'end' => 'datetime'];

    public function channel() { return $this->belongsTo(Channel::class); }
}
