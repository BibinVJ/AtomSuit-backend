<?php

namespace App\Models;

use App\Traits\AppAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemPrice extends Model
{
    use AppAudit, SoftDeletes;

    protected $fillable = [
        'price_list_id',
        'item_id',
        'price',
        'min_quantity',
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
