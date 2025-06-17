<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_id',
        'order',
        'background_color',
        'background_image',
        'title',
        'subtitle',
        'description',
        'type',
        'content',
        'is_active',
    ];

    protected $casts = [
        'content'    => 'array',
        'is_active'  => 'boolean',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }
}
