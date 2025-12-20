<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property \App\Models\DashboardCard|null $card
 */
class DashboardLayout extends Model
{
    protected $fillable = [
        'user_id',
        'dashboard_card_id',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(DashboardCard::class, 'dashboard_card_id');
    }
}
