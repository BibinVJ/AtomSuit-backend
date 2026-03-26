<?php

namespace App\Models;

use App\Enums\CustomerPaymentStatus;
use App\Traits\AppAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerPayment extends Model
{
    use AppAudit, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'account_id',
        'payment_number',
        'cost_center_id',
        'amount',
        'allocated_amount',
        'payment_date',
        'payment_method',
        'status',
        'reference_number',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'status' => CustomerPaymentStatus::class,
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(CustomerPaymentAllocation::class);
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }
}
