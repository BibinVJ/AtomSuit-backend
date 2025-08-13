<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\UserProfileAddressRequest;
use App\Http\Requests\UserProfileImageUpdateRequest;
use App\Http\Requests\UserProfileUpdateRequest;
use App\Http\Requests\UserSocialLinksRequest;
use App\Http\Resources\UserResource;
use App\Services\UserProfileService;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends Controller
{
    public function __construct(
        protected UserProfileService $userProfileService,
    ) {}

    public function show()
    {
        $user = $this->userProfileService->getProfile(Auth::user());

        return ApiResponse::success('Profile fetched.', new UserResource($user));
    }

    public function update(UserProfileUpdateRequest $request)
    {
        $user = $this->userProfileService->updateProfile(Auth::user(), $request->validated());

        return ApiResponse::success('Profile updated.', new UserResource($user));
    }

    public function updateAddress(UserProfileAddressRequest $request)
    {
        $user = $this->userProfileService->updateAddress(Auth::user(), $request->validated());

        return ApiResponse::success('Address updated.', new UserResource($user));
    }

    public function updateSocialLinks(UserSocialLinksRequest $request)
    {
        $user = $this->userProfileService->updateSocialLinks(Auth::user(), $request->validated());

        return ApiResponse::success('Social links updated.', new UserResource($user));
    }

    public function updateProfileImage(UserProfileImageUpdateRequest $request)
    {
        $user = $this->userProfileService->updateProfileImage(Auth::user(), $request->validated());

        return ApiResponse::success('Profile image updated.', new UserResource($user));
    }

    public function removeProfileImage()
    {
        $this->userProfileService->removeProfileImage(Auth::user());

        return ApiResponse::success('Profile image removed.');
    }
}
