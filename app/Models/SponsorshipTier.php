<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SponsorshipTier extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'icon',
        'image',
        'benefits',
        'is_active',
    ];

    protected $casts = [
        'benefits' => 'array',
        'is_active' => 'boolean',
    ];
}
