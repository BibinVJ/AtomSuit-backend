<?php

namespace App\Observers;

use App\Models\Sale;
use Illuminate\Support\Facades\Auth;

class SaleObserver
{
    public function creating(Sale $sale)
    {
        if (! $sale->user_id) {
            $sale->user_id = Auth::id();
        }
    }

    public function updating(Sale $sale)
    {
        if (! $sale->user_id) {
            $sale->user_id = Auth::id();
        }
    }
}
