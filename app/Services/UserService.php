<?php

namespace App\Services;

use App\Enums\UserStatus;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly FileUploadService $fileUploadService,
    ) {}

    public function create(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => UserStatus::ACTIVE,
            'phone' => $data['phone'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        if (isset($data['role_id'])) {
            $user->assignRole($data['role_id']);
        }

        return $user;
    }

    public function update(User $user, array $data): User
    {
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        if (isset($data['password'])) {
            $user->update(['password' => Hash::make($data['password'])]);
        }

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

    public function updateProfile(User $user, array $data)
    {
        return $this->userRepository->update($user, $data);
    }

    public function updateProfileImage(User $user, array $data)
    {
        if (isset($data['profile_image']) && $data['profile_image'] instanceof UploadedFile) {
            $data['profile_image'] = $this->fileUploadService->uploadToS3($data['profile_image'], User::PROFILE_IMAGE_PATH);
        }

        return $this->userRepository->update($user, $data);
    }

    public function removeProfileImage(User $user): void
    {
        if (! empty($user->profile_image)) {
            $this->fileUploadService->deleteFromS3($user->profile_image);
            $this->userRepository->update($user, ['profile_image' => null]);
        }
    }
}
