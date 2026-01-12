<?php

namespace App\Models;

use App\Traits\AppAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemPrice extends Model
{
    use AppAudit, HasFactory, SoftDeletes;

    protected $fillable = [
        'price_list_id',
        'item_id',
        'price',
        'min_quantity',
    ];

    protected $casts = [
        'price' => 'decimal:4',
        'min_quantity' => 'decimal:4',
    ];

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    // Alias for 'price' to standardized amount access if needed
    public function getAmountAttribute()
    {
        return $this->price;
    }
}
