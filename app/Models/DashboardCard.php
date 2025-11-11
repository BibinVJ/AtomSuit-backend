<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardCard extends Model
{
    protected $fillable = [
        'card_id',
        'title',
        'component',
        'description',
        'permission',
        'default_width',
        'default_height',
        'default_x',
        'default_y',
        'default_order',
        'default_config',
        'is_active',
    ];

    protected $casts = [
        'default_config' => 'array',
        'is_active' => 'boolean',
    ];
}
