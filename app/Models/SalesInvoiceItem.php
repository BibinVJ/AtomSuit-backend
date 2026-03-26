<?php

namespace App\Models;

use App\Enums\DiscountType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesInvoiceItem extends Model
{
    protected $fillable = [
        'sales_invoice_id',
        'delivery_note_item_id',
        'sales_order_item_id',
        'item_id',
        'item_meta',
        'tax_meta',
        'description',
        'quantity',
        'unit_price',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_group_id',
        'tax_amount',
        'sub_total',
        'total_amount',
    ];

    protected $casts = [
        'item_meta' => 'array',
        'tax_meta' => 'array',
        'discount_type' => DiscountType::class,
    ];

    public function salesInvoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
