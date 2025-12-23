<?php

namespace App\Models;

use App\Traits\AppAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use AppAudit, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'sales_account_id',
        'cogs_account_id',
        'inventory_account_id',
        'inventory_adjustment_account_id',
        'purchase_account_id',
    ];

    protected $casts = [
    ];

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function salesAccount(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'sales_account_id');
    }

    public function cogsAccount(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'cogs_account_id');
    }

    public function inventoryAccount(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'inventory_account_id');
    }

    public function inventoryAdjustmentAccount(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'inventory_adjustment_account_id');
    }

    public function purchaseAccount(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'purchase_account_id');
    }
}
