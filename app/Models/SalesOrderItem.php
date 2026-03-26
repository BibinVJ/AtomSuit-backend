<?php

namespace App\Models;

use App\Enums\DiscountType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesOrderItem extends Model
{
    protected $fillable = [
        'sales_order_id',
        'item_id',
        'item_meta',
        'description',
        'quantity',
        'unit_price',
        'discount_type',
        'discount_value',
        'discount_amount',
        'sub_total',
        'tax_group_id',
        'tax_meta',
        'tax_amount',
        'total_amount',
    ];

    protected $casts = [
        'item_meta' => 'array',
        'tax_meta' => 'array',
        'discount_type' => DiscountType::class,
    ];

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
