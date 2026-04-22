<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = ['name', 'email', 'password', 'status', 'max_devices'];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array { return ['email_verified_at' => 'datetime', 'password' => 'hashed']; }

    public function profiles() { return $this->hasMany(Profile::class); }
    public function devices() { return $this->hasMany(Device::class); }
    public function deviceSessions() { return $this->hasMany(DeviceSession::class); }
    public function externalSources() { return $this->hasMany(ExternalSource::class); }
}
