<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'vendor_id',
        'invoice_number',
        'purchase_date',
        'payment_status',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'payment_status' => PaymentStatus::class,
    ];

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the total cost of the purchase.
     */
    public function getTotalAttribute(): float
    {
        return $this->items->sum(fn($i) => $i->quantity * $i->unit_cost);
    }
}
