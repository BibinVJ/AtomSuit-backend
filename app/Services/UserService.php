<?php

namespace App\Services;

use App\Enums\UserStatus;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {}

    public function create(array $data): User
    {
        $date['password'] = Hash::make($data['password']);
        $data['status'] = UserStatus::ACTIVE->value;
        $user = User::create($data);

        if (isset($data['role_id'])) {
            $user->assignRole($data['role_id']);
        }

        return $user;
    }

    public function update(User $user, array $data): User
    {
        // Handle password safely
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']); // prevent null from overriding existing
        }

        $user->update($data);

        if (isset($data['role_id'])) {
            $user->syncRoles($data['role_id']);
        }

        return $user;
    }

    public function delete(User $user)
    {
        // add check for if user is deletable

        $this->userRepository->delete($user);
    }
}
