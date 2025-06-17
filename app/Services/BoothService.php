<?php

namespace App\Services;

use App\Models\Booth;
use Exception;

class BoothService
{
    public function ensureBoothIsDeletable(Booth $booth)
    {
        if ($booth->bookings()->exists()) {
            throw new Exception('Booth is booked and cannot be deleted.');
        }
    }
}