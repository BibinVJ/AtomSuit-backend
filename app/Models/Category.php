<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\AppAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use SoftDeletes, AppAudit;
    protected $fillable = [
        'name',
        'description',
    ];

    protected $casts = [
    ];

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
