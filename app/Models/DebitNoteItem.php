<?php

namespace App\Models;

use App\Enums\DiscountType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DebitNoteItem extends Model
{
    protected $fillable = [
        'debit_note_id',
        'item_id',
        'batch_id',
        'item_meta',
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
        'description',
        'is_stock_returned',
    ];

    protected $casts = [
        'is_stock_returned' => 'boolean',
        'item_meta' => 'array',
        'tax_meta' => 'array',
        'discount_type' => DiscountType::class,
    ];

    public function debitNote(): BelongsTo
    {
        return $this->belongsTo(DebitNote::class);
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
