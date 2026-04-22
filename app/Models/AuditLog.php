<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = ['subject_type', 'subject_id', 'action', 'payload', 'ip_address', 'user_agent'];
    protected $casts = ['payload' => 'array'];
}
