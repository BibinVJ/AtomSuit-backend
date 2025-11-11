<?php

namespace App\Repositories;

use App\Models\Setting;
use App\Repositories\Traits\HasCrudRepository;
use Illuminate\Database\Eloquent\Builder;

class SettingRepository
{
    use HasCrudRepository;

    public function __construct()
    {
        $this->model = new Setting();
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('key', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['group'])) {
            $query->where('group', $filters['group']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query;
    }

    /**
     * Get all settings grouped by group.
     */
    public function getAllGrouped(): array
    {
        return Setting::orderBy('group')
            ->orderBy('key')
            ->get()
            ->groupBy('group')
            ->map(function ($settings) {
                return $settings->map(function ($setting) {
                    return [
                        'id' => $setting->id,
                        'key' => $setting->key,
                        'value' => $setting->value,
                        'type' => $setting->type,
                        'description' => $setting->description,
                    ];
                });
            })
            ->toArray();
    }
}
