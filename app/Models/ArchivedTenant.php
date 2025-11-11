<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArchivedTenant extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
        'domain',
        'plan_name',
        'plan_price',
        'stripe_id',
        'stripe_subscription_id',
        'registered_at',
        'deleted_at',
        'deletion_reason',
        'metadata',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'deleted_at' => 'datetime',
        'metadata' => 'array',
        'plan_price' => 'decimal:2',
    ];
}
