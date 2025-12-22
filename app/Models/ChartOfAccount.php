<?php

namespace App\Models;

use App\Traits\AppAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChartOfAccount extends Model
{
    use AppAudit, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'account_group_id',
        'description',
        'opening_balance',
        'opening_balance',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
    ];

    public function accountGroup(): BelongsTo
    {
        return $this->belongsTo(AccountGroup::class)->withTrashed();
    }
}
