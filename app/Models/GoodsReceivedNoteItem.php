<?php

namespace App\Models;

use App\Enums\DiscountType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReceivedNoteItem extends Model
{
    protected $fillable = [
        'goods_received_note_id',
        'item_id',
        'batch_id',
        'item_meta',
        'purchase_order_item_id',
        'description',
        'quantity_received',
        'accepted_quantity',
        'rejected_quantity',
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

    public function goodsReceivedNote(): BelongsTo
    {
        return $this->belongsTo(GoodsReceivedNote::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
}
