<?php

namespace App\Models;

use App\Enums\DiscountType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditNoteItem extends Model
{
    protected $fillable = [
        'credit_note_id',
        'sales_invoice_item_id',
        'item_id',
        'batch_id',
        'item_meta',
        'tax_meta',
        'description',
        'returned_quantity',
        'is_stock_returned',
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
        'is_stock_returned' => 'boolean',
        'item_meta' => 'array',
        'tax_meta' => 'array',
        'discount_type' => DiscountType::class,
    ];

    public function creditNote(): BelongsTo
    {
        return $this->belongsTo(CreditNote::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
}
