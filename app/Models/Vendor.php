<?php

namespace App\Models;

use App\Traits\AppAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use AppAudit, HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'currency_id',
        'payables_account_id',
        'purchase_account_id',
        'purchase_discount_account_id',
        'purchase_return_account_id',
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

    public function payablesAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'payables_account_id')->withTrashed();
    }

    public function purchaseAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'purchase_account_id')->withTrashed();
    }

    public function purchaseDiscountAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'purchase_discount_account_id')->withTrashed();
    }

    public function purchaseReturnAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'purchase_return_account_id')->withTrashed();
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }
}
