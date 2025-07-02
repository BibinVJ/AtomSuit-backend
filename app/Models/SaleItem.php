<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'item_id',
        'quantity',
        'unit_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function getBatchesAttribute(): Collection
    {
        return Batch::whereHas('stockMovements', function ($query) {
            $query->where('source_type', Sale::class)
                ->where('source_id', $this->sale_id)
                ->where('item_id', $this->item_id)
                ->where('quantity', '<', 0);
        })->get();
    }
}
