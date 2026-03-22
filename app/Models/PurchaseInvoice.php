<?php

namespace App\Models;

use App\Enums\PurchaseInvoiceStatus;
use App\Traits\AppAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseInvoice extends Model
{
    use AppAudit, SoftDeletes;

    protected $fillable = [
        'grn_id',
        'purchase_order_id',
        'vendor_id',
        'invoice_number',
        'reference_number',
        'posting_date',
        'due_date',
        'status',
        'cost_center_id',
        'warehouse_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'posting_date' => 'date',
        'due_date' => 'date',
        'status' => PurchaseInvoiceStatus::class,
    ];

    public function grn(): BelongsTo
    {
        return $this->belongsTo(GoodsReceivedNote::class, 'grn_id');
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
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
        return $this->hasMany(PurchaseInvoiceItem::class);
    }

    public function transactions(): MorphMany
    {
        return $this->morphMany(GeneralLedgerTransaction::class, 'reference');
    }
}
