<?php

namespace App\Models;

use App\Enums\GoodsReceivedNoteStatus;
use App\Traits\AppAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoodsReceivedNote extends Model
{
    use AppAudit, SoftDeletes;

    protected $fillable = [
        'purchase_order_id',
        'vendor_id',
        'vendor_meta',
        'grn_number',
        'reference_number',
        'received_date',
        'status',
        'notes',
        'sub_total',
        'discount_total',
        'tax_total',
        'total_amount',
        'cost_center_id',
        'warehouse_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'received_date' => 'date',
        'status' => GoodsReceivedNoteStatus::class,
        'vendor_meta' => 'array',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(GoodsReceivedNoteItem::class);
    }

    public function purchaseInvoices(): HasMany
    {
        return $this->hasMany(PurchaseInvoice::class, 'grn_id');
    }

    public function stockMovements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'source');
    }
}
