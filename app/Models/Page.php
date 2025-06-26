<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'title',
        'meta_title',
        'meta_description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    public function sections(): HasMany
    {
        return $this->hasMany(Section::class)->orderBy('order');
    }
}
