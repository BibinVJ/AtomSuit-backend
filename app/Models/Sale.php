<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Sale extends Model
{
    protected $fillable = [
        'customer_id',
        'invoice_number',
        'sale_date',
        'payment_status',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'status' => TransactionStatus::class,
        'payment_status' => PaymentStatus::class,
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockMovements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'source');
    }

    /**
     * Get the total cost of the sale.
     */
    public function getTotalAttribute(): float
    {
        return $this->items->sum(fn ($i) => $i->quantity * $i->unit_price);
    }
}
