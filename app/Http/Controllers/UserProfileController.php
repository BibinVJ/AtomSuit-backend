<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\UserProfileImageUpdateRequest;
use App\Http\Requests\UserProfileUpdateRequest;
use App\Http\Resources\UserResource;
use App\Services\UserProfileService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends Controller
{
    public function __construct(
        protected UserService $userService,
    ) { }

    public function show()
    {
        return ApiResponse::success('User profile fetched.', new UserResource(Auth::user()));
    }

    public function update(UserProfileUpdateRequest $request)
    {
        $updatedUser = $this->userService->updateProfile(Auth::user(), $request->validated());
        return ApiResponse::success('User profile updated.', new UserResource($updatedUser));
    }

    public function updateProfileImage(UserProfileImageUpdateRequest $request)
    {
        $this->userService->updateProfileImage(Auth::user(), $request->validated());
        return ApiResponse::success('Profile image updated.');
    }

    public function removeProfileImage()
    {
        $this->userService->removeProfileImage(Auth::user());
        return ApiResponse::success('Profile image removed.');
    }
}
