<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Traits\HasCrudRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserRepository
{
    use HasCrudRepository;

    public function __construct()
    {
        $this->model = new User();
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (isset($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('email', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['role'])) {
            $query->whereHas('roles', function ($q) use ($filters) {
                $q->where('name', $filters['role']);
            });
        }

        if (!empty($filters['exclude_current']) && $filters['exclude_current'] === true) {
            $query->where('id', '!=', Auth::id());
        }

        return $query;
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function updatePassword(User $user, string $password): void
    {
        $user->update(['password' => Hash::make($password)]);
    }

    public function userCount(?array $statuses = null, ?array $roles = null): int
    {
        return User::when($statuses, fn($query) => $query->whereIn('status', $statuses))
            ->when($roles, fn($query) => $query->whereHas('roles', fn($q) => $q->whereIn('name', $roles)))
            ->count();
    }

    public function changeStatus(User $user, string $status)
    {
        $user->update(['status' => $status, 'status_updated_at' => now()]);
    }
}
