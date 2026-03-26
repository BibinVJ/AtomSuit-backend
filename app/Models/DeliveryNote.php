<?php

namespace App\Models;

use App\Enums\DeliveryNoteStatus;
use App\Traits\AppAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryNote extends Model
{
    use AppAudit, SoftDeletes;

    protected $fillable = [
        'sales_order_id',
        'customer_id',
        'customer_meta',
        'dn_number',
        'reference_number',
        'dispatch_date',
        'status',
        'sub_total',
        'discount_total',
        'tax_total',
        'total_amount',
        'cost_center_id',
        'warehouse_id',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'dispatch_date' => 'date',
        'customer_meta' => 'array',
        'status' => DeliveryNoteStatus::class,
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryNoteItem::class);
    }

    public function stockMovements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'source');
    }

    public function salesInvoices(): HasMany
    {
        return $this->hasMany(SalesInvoice::class);
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
