<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

class PermissionService
{
    public function getAll(array $filters = []): Collection
    {
        $query = Permission::query();

        // Optional search filter
        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%'.$filters['search'].'%');
        }

        return $query->get();
    }
}
