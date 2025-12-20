<?php

namespace App\Repositories\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

trait HasCrudRepository
{
    protected Model $model;

    public function all(
        bool $paginate = false,
        int $perPage = 15,
        array $filters = [],
        array $with = [],
    ): Collection|\Illuminate\Contracts\Pagination\LengthAwarePaginator|array {

        $query = $this->model->newQuery();

        // Eager load relationships
        if (! empty($with)) {
            $query->with($with);
        }

        // Apply global filters (trashed, etc.)
        $query = $this->applyGlobalFilters($query, $filters);

        // Optional hook to apply specific filters
        if (method_exists($this, 'applyFilters')) {
            $query = $this->applyFilters($query, $filters);
        }

        // for sort
        $sortBy = $filters['sort_by'] ?? 'id';
        $sortDir = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortBy, $sortDir);

        // check for from and to for range based fetching
        if (isset($filters['from']) && isset($filters['to'])) {
            $from = (int) $filters['from'];
            $to = (int) $filters['to'];
            if ($to >= $from) {
                $total = $query->count();
                $skip = max(0, $from - 1);
                $take = $to - $from + 1;
                $data = $query->skip($skip)->take($take)->get();
                
                return [
                    'data' => $data,
                    'total' => $total
                ];
            }
        }

        return $paginate ? $query->paginate($perPage) : $query->get();
    }

    public function find(int $id, array $with = []): ?Model
    {
        return $this->model->with($with)->find($id);
    }

    public function findOrFail(int $id, array $with = []): Model
    {
        return $this->model->with($with)->findOrFail($id);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data)->refresh();
    }

    public function firstOrCreate(array $attributes, array $values = []): Model
    {
        return $this->model->firstOrCreate($attributes, $values);
    }

    public function update(Model $model, array $data): Model
    {
        $model->update($data);

        return $model;
    }

    public function delete(Model $model): void
    {
        $model->delete();
    }

    public function deleteMany(array $ids): void
    {
        $this->model->whereIn('id', $ids)->delete();
    }

    public function restore(Model $model): bool
    {
        return $model->restore();
    }

    public function forceDelete(Model $model): bool
    {
        return $model->forceDelete();
    }

    protected function applyGlobalFilters(\Illuminate\Database\Eloquent\Builder $query, array $filters): \Illuminate\Database\Eloquent\Builder
    {
        if (isset($filters['trashed'])) {
            if ($filters['trashed'] === 'only') {
                $query->onlyTrashed();
            } elseif ($filters['trashed'] === 'with') {
                $query->withTrashed();
            }
        }

        return $query;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
