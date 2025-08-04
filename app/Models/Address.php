<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Address extends Model
{
    protected $fillable = [
        'addressable_id',
        'addressable_type',
        'type',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'country',
        'postal_code',
    ];

    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }
}
