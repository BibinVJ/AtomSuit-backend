<?php

namespace App\Models;

use App\Enums\DebitNoteStatus;
use App\Traits\AppAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DebitNote extends Model
{
    use AppAudit, SoftDeletes;

    protected $fillable = [
        'vendor_id',
        'vendor_meta',
        'purchase_invoice_id',
        'debit_note_number',
        'reference_number',
        'date',
        'status',
        'notes',
        'sub_total',
        'discount_total',
        'tax_total',
        'total_amount',
        'warehouse_id',
        'cost_center_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date' => 'date',
        'status' => DebitNoteStatus::class,
        'vendor_meta' => 'array',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function purchaseInvoice(): BelongsTo
    {
        return $this->belongsTo(PurchaseInvoice::class);
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DebitNoteItem::class);
    }

    public function stockMovements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'source');
    }
}
