<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;

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
        'payment_status' => PaymentStatus::class,
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Get the total cost of the purchase.
     */
    public function getTotalAttribute(): float
    {
        return $this->items->sum(fn($i) => $i->quantity * $i->unit_price);
    }
}
