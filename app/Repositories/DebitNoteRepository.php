<?php

namespace App\Repositories;

use App\Models\DebitNote;
use App\Repositories\Traits\HasCrudRepository;
use Illuminate\Database\Eloquent\Builder;

class DebitNoteRepository
{
    use HasCrudRepository;

    public function __construct()
    {
        $this->model = new DebitNote;
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('debit_note_number', 'like', '%'.$filters['search'].'%')
                    ->orWhere('reference_number', 'like', '%'.$filters['search'].'%')
                    ->orWhereHas('vendor', fn ($q) => $q->where('name', 'like', "%{$filters['search']}%"));
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }

        return $query;
    }
}
