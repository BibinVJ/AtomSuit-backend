<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardCard extends Model
{
    protected $fillable = [
        'slug',
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
    ];

    protected $casts = [
        'default_config' => 'array',
    ];
}
