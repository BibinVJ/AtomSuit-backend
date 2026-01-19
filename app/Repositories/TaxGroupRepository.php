<?php

namespace App\Repositories;

use App\Models\TaxGroup;
use App\Repositories\Traits\HasCrudRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TaxGroupRepository
{
    use HasCrudRepository;

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

    public function create(array $data): Model
    {
        $taxGroup = $this->create($data);

        /** @var \App\Models\TaxGroup $taxGroup */
        if (isset($data['tax_rates'])) {
            $taxGroup->taxRates()->sync($data['tax_rates']);
        }

        return $taxGroup->load('taxRates');
    }

    public function update(Model $model, array $data): Model
    {
        $taxGroup = $this->update($model, $data);

        /** @var \App\Models\TaxGroup $taxGroup */
        if (isset($data['tax_rates'])) {
            $taxGroup->taxRates()->sync($data['tax_rates']);
        }

        return $taxGroup->load('taxRates');
    }
}
