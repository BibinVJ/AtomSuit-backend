<?php

namespace App\Repositories;

use App\Http\Resources\SettingResource;
use App\Models\Setting;
use App\Repositories\Traits\HasCrudRepository;
use Illuminate\Database\Eloquent\Builder;

class SettingRepository
{
    use HasCrudRepository;

    public function __construct()
    {
        $this->model = new Setting;
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('key', 'like', '%'.$filters['search'].'%')
                    ->orWhere('description', 'like', '%'.$filters['search'].'%');
            });
        }

        if (! empty($filters['group'])) {
            $query->where('group', $filters['group']);
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query;
    }

    /**
     * Get all settings grouped by group.
     */
    public function getAllGrouped(): array
    {
        $settings = Setting::orderBy('id')->get();
        $currencySetting = $settings->where('key', 'currency')->first();

        if ($currencySetting) {
            $currency = \App\Models\Currency::find($currencySetting->value);
            if ($currency) {
                if (! $settings->where('key', 'currency_symbol')->first()) {
                    $currencySymbol = new Setting(['key' => 'currency_symbol', 'value' => $currency->symbol, 'type' => 'string', 'group' => 'payment']);
                    $currencySymbol->id = 9991; // Dummy ID to prevent null pointer exceptions
                    $settings->push($currencySymbol);
                }
                if (! $settings->where('key', 'currency_code')->first()) {
                    $currencyCode = new Setting(['key' => 'currency_code', 'value' => $currency->code, 'type' => 'string', 'group' => 'payment']);
                    $currencyCode->id = 9992; // Dummy ID
                    $settings->push($currencyCode);
                }
            }
        }

        return $settings->groupBy('group')
            ->map(fn ($groupSettings) => SettingResource::collection($groupSettings))
            ->toArray();
    }
}
