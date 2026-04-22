<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = ['user_id', 'name', 'avatar', 'is_kid', 'pin'];
    protected $hidden = ['pin'];
    protected $casts = ['is_kid' => 'boolean'];

    public function user() { return $this->belongsTo(User::class); }
    public function favorites() { return $this->hasMany(Favorite::class); }
}
