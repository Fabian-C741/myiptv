<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StremioAddon extends Model
{
    protected $fillable = [
        'name',
        'manifest_url',
        'icon',
        'catalog_types',
        'is_active'
    ];

    protected $casts = [
        'catalog_types' => 'array',
        'is_active' => 'boolean'
    ];
}
