<?php

namespace App\Repositories;

use App\Models\TaxGroup;
use App\Repositories\Traits\HasCrudRepository;
use Illuminate\Database\Eloquent\Builder;

class TaxGroupRepository
{
    use HasCrudRepository {
        create as createTrait;
        update as updateTrait;
    }

    public function __construct()
    {
        $this->model = new TaxGroup;
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%'.$filters['search'].'%');
        }

        return $query;
    }

    public function create(array $data): \Illuminate\Database\Eloquent\Model
    {
        $taxGroup = $this->createTrait($data);

        /** @var \App\Models\TaxGroup $taxGroup */
        if (isset($data['tax_rates'])) {
            $taxGroup->taxRates()->sync($data['tax_rates']);
        }

        return $taxGroup->load('taxRates');
    }

    public function update(\Illuminate\Database\Eloquent\Model $model, array $data): \Illuminate\Database\Eloquent\Model
    {
        $taxGroup = $this->updateTrait($model, $data);

        /** @var \App\Models\TaxGroup $taxGroup */
        if (isset($data['tax_rates'])) {
            $taxGroup->taxRates()->sync($data['tax_rates']);
        }

        return $taxGroup->load('taxRates');
    }
}
