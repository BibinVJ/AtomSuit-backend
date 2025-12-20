<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\AppAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use SoftDeletes, AppAudit;
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
