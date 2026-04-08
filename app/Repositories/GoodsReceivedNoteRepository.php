<?php

namespace App\Repositories;

use App\Models\GoodsReceivedNote;
use App\Repositories\Traits\HasCrudRepository;
use Illuminate\Database\Eloquent\Builder;

class GoodsReceivedNoteRepository
{
    use HasCrudRepository;

    public function __construct()
    {
        $this->model = new GoodsReceivedNote;
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('grn_number', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%")
                    ->orWhereHas('vendor', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('purchaseOrder', fn ($q) => $q->where('order_number', 'like', "%{$search}%"));
            });
        }

        if (! empty($filters['status'])) {
            if (is_array($filters['status'])) {
                $query->whereIn('status', $filters['status']);
            } else {
                $query->where('status', $filters['status']);
            }
        }

        if (! empty($filters['exclude_status'])) {
            if (is_array($filters['exclude_status'])) {
                $query->whereNotIn('status', $filters['exclude_status']);
            } else {
                $query->where('status', '!=', $filters['exclude_status']);
            }
        }

        if (! empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        if (! empty($filters['purchase_order_id'])) {
            $query->where('purchase_order_id', $filters['purchase_order_id']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('received_date', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('received_date', '<=', $filters['date_to']);
        }

        return $query;
    }
}
