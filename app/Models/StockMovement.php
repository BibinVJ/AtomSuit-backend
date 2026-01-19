<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = [
        'item_id',
        'batch_id',
        'transaction_date',
        'quantity',
        'rate',
        'standard_cost',
        'source_type',
        'source_id',
        'description',
        'reference',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'quantity' => 'integer',
        'rate' => 'decimal',
        'standard_cost' => 'decimal',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function source()
    {
        return $this->morphTo();
    }
}
