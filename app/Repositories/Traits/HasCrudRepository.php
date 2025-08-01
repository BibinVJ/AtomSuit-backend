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
    ): Collection|\Illuminate\Contracts\Pagination\LengthAwarePaginator {

        $query = $this->model->newQuery();

        // Eager load relationships
        if (! empty($with)) {
            $query->with($with);
        }

        // Optional hook to apply filters
        if (method_exists($this, 'applyFilters')) {
            $query = $this->applyFilters($query, $filters);
        }

        // for sort
        $sortBy = $filters['sort_by'] ?? 'id';
        $sortDir = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortBy, $sortDir);

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

    public function firstOrCreate(array $data): Model
    {
        return $this->model->firstOrCreate($data);
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
}
