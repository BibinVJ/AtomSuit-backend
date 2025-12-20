<?php

namespace App\Models;

use App\Traits\AppAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Currency extends Model
{
    use AppAudit, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'is_default',
        'thousand_separator',
        'decimal_separator',
        'precision',
        'symbol_position',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'precision' => 'integer',
    ];

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function vendors()
    {
        return $this->hasMany(Vendor::class);
    }

    public function baseExchangeRates(): HasMany
    {
        return $this->hasMany(ExchangeRate::class, 'base_currency_id');
    }

    public function targetExchangeRates(): HasMany
    {
        return $this->hasMany(ExchangeRate::class, 'target_currency_id');
    }
}
