<?php

namespace App\Services;

use App\Models\CentralUser;
use App\Models\User;
use App\Models\UserDetail;
use App\Repositories\UserRepository;

class UserProfileService extends ContextAwareService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly FileUploadService $fileUploadService,
    ) {}

    public function getProfile(User|CentralUser $user): User|CentralUser
    {
        if ($this->isCentralContext()) {
            return $user;
        } else {
            return $user->load(['detail', 'addresses', 'socialLinks']);
        }
    }

    public function updateProfile(User $user, array $data): User
    {
        // for now only name change is allowed in users table
        $this->userRepository->update($user, $data);
        $user->detail()->updateOrCreate([], $data);

        return $user->refresh()->load(['detail']);
    }

    public function updateAddress(User $user, array $data): User
    {
        $user->addresses()->updateOrCreate(['type' => $data['type']], $data);

        return $user->refresh()->load(['addresses']);
    }

    public function updateSocialLinks(User $user, array $data): User
    {
        foreach ($data as $platform => $url) {
            $user->socialLinks()->updateOrCreate(
                ['platform' => $platform],
                ['url' => $url]
            );
        }

        return $user->refresh()->load(['socialLinks']);
    }

    public function updateProfileImage(User $user, array $data): User
    {
        $imagePath = $this->fileUploadService->uploadToS3($data['profile_image'], UserDetail::PROFILE_IMAGE_PATH);
        $user->detail()->updateOrCreate([], ['profile_image' => $imagePath]);

        return $user->refresh();
    }

    public function removeProfileImage(User $user): void
    {
        if (! empty($user->profile_image)) {
            $this->fileUploadService->deleteFromS3($user->profile_image);
            $user->detail()->updateOrCreate([], ['profile_image' => null]);
        }
    }
}
