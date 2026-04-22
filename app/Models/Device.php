<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = ['user_id', 'device_id', 'device_name', 'device_type', 'os_version', 'ip_address', 'country', 'region', 'city', 'last_access', 'is_active'];
    protected $casts = ['is_active' => 'boolean', 'last_access' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
    public function sessions() { return $this->hasMany(DeviceSession::class); }
}
