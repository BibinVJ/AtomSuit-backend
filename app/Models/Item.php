<?php

namespace App\Models;

use App\Enums\ItemType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    protected $fillable = [
        'sku',
        'name',
        'category_id',
        'unit_id',
        'description',
        'type',
        'sales_account_id',
        'cogs_account_id',
        'inventory_account_id',
        'inventory_adjustment_account_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'type' => ItemType::class,
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function salesAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'sales_account_id');
    }

    public function cogsAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'cogs_account_id');
    }

    public function inventoryAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'inventory_account_id');
    }

    public function inventoryAdjustmentAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'inventory_adjustment_account_id');
    }
}
