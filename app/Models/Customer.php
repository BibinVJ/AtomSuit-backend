<?php

namespace App\Models;

use App\Traits\AppAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes; // Added this line as SoftDeletes is used in the trait declaration

class Customer extends Model
{
    use AppAudit, HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'currency_id',
        'sales_account_id',
        'sales_discount_account_id',
        'receivables_account_id',
        'sales_return_account_id',
        'billing_address_line_1',
        'billing_address_line_2',
        'billing_city',
        'billing_state',
        'billing_country',
        'billing_zip_code',
        'shipping_address_line_1',
        'shipping_address_line_2',
        'shipping_city',
        'shipping_state',
        'shipping_country',
        'shipping_zip_code',
    ];

    protected $casts = [
    ];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function salesAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'sales_account_id')->withTrashed();
    }

    public function salesDiscountAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'sales_discount_account_id')->withTrashed();
    }

    public function receivablesAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'receivables_account_id')->withTrashed();
    }

    public function salesReturnAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'sales_return_account_id')->withTrashed();
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function totalSpent(): float
    {
        return (float) $this->sales->sum(fn (Sale $sale) => $sale->total);
    }
}
