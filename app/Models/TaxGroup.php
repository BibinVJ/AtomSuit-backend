<?php

namespace App\Models;

use App\Traits\AppAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxGroup extends Model
{
    use AppAudit, SoftDeletes;

    protected $fillable = [
        'name',
    ];

    public function taxRates(): BelongsToMany
    {
        return $this->belongsToMany(TaxRate::class, 'tax_group_rates')->withTrashed();
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function vendors(): HasMany
    {
        return $this->hasMany(Vendor::class);
    }
}
