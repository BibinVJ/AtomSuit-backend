<?php

namespace App\Services;

use Exception;
use Illuminate\Database\Eloquent\Model;

abstract class BaseService
{
    /**
     * @var mixed
     */
    protected $repository;

    public function delete(Model $model, bool $force = false)
    {
        if ($force) {
            $this->validateForceDelete($model);

            return $this->repository->forceDelete($model);
        }

        return $this->repository->delete($model);
    }

    public function restore(Model $model): Model
    {
        $this->repository->restore($model);

        return $model;
    }

    /**
     * Optional hook to validate if force delete is allowed.
     * Throw an exception if not.
     */
    protected function validateForceDelete(Model $model): void
    {
        // Override in child classes if needed
    }
}
