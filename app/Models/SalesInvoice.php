<?php

namespace App\Models;

use App\Enums\SalesInvoiceStatus;
use App\Traits\AppAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesInvoice extends Model
{
    use AppAudit, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'customer_meta',
        'invoice_number',
        'invoice_date',
        'due_date',
        'status',
        'sub_total',
        'discount_total',
        'tax_total',
        'total_amount',
        'paid_amount',
        'due_amount',
        'cost_center_id',
        'notes',
        'sales_order_id',
        'delivery_note_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'customer_meta' => 'array',
        'status' => SalesInvoiceStatus::class,
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesInvoiceItem::class);
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function deliveryNote(): BelongsTo
    {
        return $this->belongsTo(DeliveryNote::class);
    }

    public function creditNotes(): HasMany
    {
        return $this->hasMany(CreditNote::class);
    }
}
