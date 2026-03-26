<?php

namespace App\Models;

use App\Enums\CreditNoteStatus;
use App\Traits\AppAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreditNote extends Model
{
    use AppAudit, SoftDeletes;

    protected $fillable = [
        'sales_invoice_id',
        'customer_id',
        'credit_note_number',
        'credit_note_date',
        'status',
        'is_stock_returned',
        'customer_meta',
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
        'credit_note_date' => 'date',
        'customer_meta' => 'array',
        'status' => CreditNoteStatus::class,
        'is_stock_returned' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesInvoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CreditNoteItem::class);
    }

    public function stockMovements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'source');
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
