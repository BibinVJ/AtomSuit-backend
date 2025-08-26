<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Purchase extends Model
{
    protected $fillable = [
        'user_id',
        'vendor_id',
        'invoice_number',
        'purchase_date',
        'payment_status',
        'note'
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'status' => TransactionStatus::class,
        'payment_status' => PaymentStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function stockMovements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'source');
    }

    /**
     * Get the total cost of the purchase.
     */
    public function getTotalAttribute(): float
    {
        return $this->items->sum(fn (PurchaseItem $i) => $i->quantity * $i->unit_cost);
    }
}
