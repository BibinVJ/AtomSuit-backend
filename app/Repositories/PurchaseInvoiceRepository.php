<?php

namespace App\Repositories;

use App\Models\PurchaseInvoice;
use App\Repositories\Traits\HasCrudRepository;
use Illuminate\Database\Eloquent\Builder;

class PurchaseInvoiceRepository
{
    use HasCrudRepository;

    public function __construct()
    {
        $this->model = new PurchaseInvoice;
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%")
                    ->orWhereHas('vendor', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('grn', fn ($q) => $q->where('grn_number', 'like', "%{$search}%"));
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        if (! empty($filters['grn_id'])) {
            $query->where('grn_id', $filters['grn_id']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('posting_date', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('posting_date', '<=', $filters['date_to']);
        }

        return $query;
    }
}
