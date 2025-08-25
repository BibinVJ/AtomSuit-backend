<?php

namespace App\Models;

use App\Enums\ItemType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    protected $fillable = [
        'sku',
        'name',
        'category_id',
        'unit_id',
        'description',
        'type',
        'selling_price',
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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Batch, \App\Models\Item>
     */
    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    // public function salesAccount(): BelongsTo
    // {
    //     return $this->belongsTo(ChartOfAccount::class, 'sales_account_id');
    // }

    // public function cogsAccount(): BelongsTo
    // {
    //     return $this->belongsTo(ChartOfAccount::class, 'cogs_account_id');
    // }

    // public function inventoryAccount(): BelongsTo
    // {
    //     return $this->belongsTo(ChartOfAccount::class, 'inventory_account_id');
    // }

    // public function inventoryAdjustmentAccount(): BelongsTo
    // {
    //     return $this->belongsTo(ChartOfAccount::class, 'inventory_adjustment_account_id');
    // }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Get the stock on hand for this item.
     */
    public function stockOnHand(): int
    {
        return $this->stockMovements->sum('quantity');
    }

    public function nonExpiredStock(): int
    {
        return $this->batches
            ->filter(fn (Batch $batch) => $batch->expiry_date?->isFuture())
            ->sum(fn (Batch $batch) => $batch->stockOnHand());
    }

    public function expiredStock(): int
    {
        return $this->batches
            ->filter(fn (Batch $batch) => $batch->expiry_date?->isPast())
            ->sum(fn (Batch $batch) => $batch->stockOnHand());
    }

    /**
     * Get the total purchased quantity for this item.
     */
    public function totalPurchased(): int
    {
        return $this->stockMovements()
            ->where('source_type', Purchase::class) // TODO: Change to GoodsReceivedNote::class, when using proper structure later
            ->sum('quantity');
    }

    /**
     * Get the total sold quantity for this item.
     */
    public function totalSold(): int
    {
        return abs($this->stockMovements()
            ->where('source_type', Sale::class) // TODO: Change to DeliveryNote::class, when using proper structure later
            ->sum('quantity'));
    }
}
