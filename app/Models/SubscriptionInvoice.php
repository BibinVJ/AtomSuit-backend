<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionInvoice extends Model
{
    protected $fillable = [
        'subscription_id',
        'amount',
        'currency',
        'payment_status',
        'transaction_id',
        'invoice_date',
        'metadata',
    ];

    protected $casts = [
        'invoice_date' => 'datetime',
        'metadata' => 'array',
        'payment_status' => PaymentStatus::class,
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
