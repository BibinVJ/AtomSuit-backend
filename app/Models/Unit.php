<?php

namespace App\Models;

use App\Traits\AppAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use AppAudit, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
    ];

    protected $casts = [
    ];

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
