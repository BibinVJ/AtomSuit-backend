<?php

namespace App\Observers;

use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;

class PurchaseObserver
{
    public function creating(Purchase $purchase)
    {
        if (! $purchase->user_id) {
            $purchase->user_id = Auth::id();
        }
    }

    public function updating(Purchase $purchase)
    {
        if (! $purchase->user_id) {
            $purchase->user_id = Auth::id();
        }
    }
}
