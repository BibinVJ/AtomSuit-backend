<?php

namespace App\Models;

use App\Traits\AppAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes; // Added this line as SoftDeletes is used in the trait declaration

class Customer extends Model
{
    use AppAudit, HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
    ];

    protected $casts = [
    ];

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function totalSpent(): float
    {
        return (float) $this->sales->sum(fn (Sale $sale) => $sale->total);
    }
}
