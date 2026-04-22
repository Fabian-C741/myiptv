<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityAlert extends Model
{
    protected $fillable = ['type', 'ip_address', 'user_id', 'description', 'resolved'];
    protected $casts = ['resolved' => 'boolean'];

    public function user() { return $this->belongsTo(User::class); }
}
