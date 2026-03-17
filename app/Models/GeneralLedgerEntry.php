<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class GeneralLedgerEntry extends Model
{
    protected $fillable = [
        'gl_transaction_id',
        'account_id',
        'debit',
        'credit',
        'description',
        'entity_type',
        'entity_id',
        'cost_center_id',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(GeneralLedgerTransaction::class, 'gl_transaction_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }
}
