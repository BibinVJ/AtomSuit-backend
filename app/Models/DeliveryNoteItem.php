<?php

namespace App\Models;

use App\Enums\DiscountType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryNoteItem extends Model
{
    protected $fillable = [
        'delivery_note_id',
        'sales_order_item_id',
        'item_id',
        'item_meta',
        'description',
        'dispatched_quantity',
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

    public function deliveryNote(): BelongsTo
    {
        return $this->belongsTo(DeliveryNote::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
