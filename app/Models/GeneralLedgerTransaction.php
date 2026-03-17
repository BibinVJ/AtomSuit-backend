<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GeneralLedgerTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference_type',
        'reference_id',
        'transaction_date',
        'description',
    ];

    protected $casts = [
        'transaction_date' => 'date',
    ];

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function entries(): HasMany
    {
        return $this->hasMany(GeneralLedgerEntry::class, 'gl_transaction_id');
    }
}
