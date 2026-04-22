<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceSession extends Model
{
    protected $fillable = ['user_id', 'profile_id', 'device_id', 'token', 'expires_at'];
    protected $casts = ['expires_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
    public function profile() { return $this->belongsTo(Profile::class); }
    public function device() { return $this->belongsTo(Device::class); }
}
