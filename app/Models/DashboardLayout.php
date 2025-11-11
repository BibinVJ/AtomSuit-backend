<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardLayout extends Model
{
    protected $fillable = [
        'user_id',
        'card_id',
        'area',
        'x',
        'y',
        'rotation',
        'width',
        'height',
        'col_span',
        'draggable',
        'visible',
        'config',
    ];

    protected $casts = [
        'x' => 'float',
        'y' => 'float',
        'rotation' => 'float',
        'width' => 'float',
        'height' => 'float',
        'col_span' => 'integer',
        'draggable' => 'boolean',
        'visible' => 'boolean',
        'config' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function card()
    {
        return $this->belongsTo(DashboardCard::class, 'card_id', 'card_id');
    }
}
