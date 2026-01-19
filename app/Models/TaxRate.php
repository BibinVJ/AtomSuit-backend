<?php

namespace App\Models;

use App\Traits\AppAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxRate extends Model
{
    use AppAudit, SoftDeletes;

    protected $fillable = [
        'name',
        'rate',
        'type',
        'sales_account_id',
        'purchase_account_id',
    ];

    public function salesAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'sales_account_id');
    }

    public function purchaseAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'purchase_account_id');
    }

    public function taxGroups(): BelongsToMany
    {
        return $this->belongsToMany(TaxGroup::class, 'tax_group_rates');
    }
}
