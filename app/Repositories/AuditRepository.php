<?php

namespace App\Repositories;

use App\Repositories\Traits\HasCrudRepository;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

class AuditRepository
{
    use HasCrudRepository;

    public function __construct()
    {
        $this->model = new Activity;
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['log_name'])) {
            $query->where('log_name', $filters['log_name']);
        }

        if (! empty($filters['event'])) {
            $query->where('event', $filters['event']);
        }

        if (! empty($filters['causer_id'])) {
            $query->where('causer_id', $filters['causer_id']);
        }

        if (! empty($filters['subject_type'])) {
            $query->where('subject_type', $filters['subject_type']);
        }

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('description', 'like', '%'.$filters['search'].'%')
                    ->orWhere('properties', 'like', '%'.$filters['search'].'%');
            });
        }

        return $query;
    }
}
