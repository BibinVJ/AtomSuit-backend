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
    ];

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function vendors(): HasMany
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
