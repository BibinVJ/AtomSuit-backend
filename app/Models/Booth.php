<?php

namespace App\Models;

use App\Enums\BoothStatus;
use Illuminate\Database\Eloquent\Model;

class Booth extends Model
{
    protected $fillable = [
        'unique_id',
        'name',
        'description',
        'image',
        'size',
        'price',
        'status',
        'is_active',
    ];

    protected $casts = [
        'price'     => 'decimal:2',
        'is_active' => 'boolean',
        'status'    => BoothStatus::class,
    ];


    /**
     * A booth can have many bookings (by different users).
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get all users who have booked this booth.
     */
    public function users()
    {
        return $this->hasManyThrough(User::class, Booking::class);
    }
}
