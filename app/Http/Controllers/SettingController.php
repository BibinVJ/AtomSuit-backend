<?php

namespace App\Http\Controllers;

use App\Enums\PermissionsEnum;
use App\Helpers\ApiResponse;
use App\Http\Requests\SettingRequest;
use App\Http\Resources\SettingResource;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SettingController extends Controller
{
    public function __construct(protected SettingService $settingService)
    {
        $this->middleware('permission:' . PermissionsEnum::VIEW_SETTING->value)->only(['index', 'show', 'getByGroup', 'groups']);
        $this->middleware('permission:' . PermissionsEnum::UPDATE_SETTING->value)->only(['update', 'bulkUpdate', 'destroy', 'deleteFile']);
    }

    /**
     * Get all settings grouped by category.
     */
    public function index()
    {
        $settings = $this->settingService->getAllGrouped();
        
        // Transform grouped settings to resources
        $transformed = collect($settings)->map(function ($groupSettings) {
            return SettingResource::collection(
                collect($groupSettings)->map(fn($s) => (object)$s)
            );
        });
        
        return ApiResponse::success(
            'Settings fetched successfully.',
            $transformed
        );
    }

    /**
     * Get settings for a specific group.
     */
    public function getByGroup(string $group)
    {
        $settings = $this->settingService->getByGroup($group);
        
        return ApiResponse::success(
            'Settings fetched successfully.',
            SettingResource::collection($settings)
        );
    }

    /**
     * Get a single setting value.
     */
    public function show(string $key)
    {
        $value = $this->settingService->get($key);
        
        if ($value === null) {
            return ApiResponse::error(
                'Setting not found.',
                Response::HTTP_NOT_FOUND
            );
        }
        
        return ApiResponse::success(
            'Setting fetched successfully.',
            [
                'key' => $key,
                'value' => $value,
            ]
        );
    }

    /**
     * Update a single setting.
     */
    public function update(SettingRequest $request, string $key)
    {
        $validated = $request->validated();

        $value = $validated['value'];
        $type = $validated['type'] ?? 'string';
        $group = $validated['group'] ?? null;

        // Handle file upload
        if ($request->hasFile('value')) {
            $value = $this->settingService->handleFileUpload($key, $request->file('value'));
            $type = 'file';
        }

        $setting = $this->settingService->set($key, $value, $type, $group);
        
        return ApiResponse::success(
            'Setting updated successfully.',
            SettingResource::make($setting)
        );
    }

    /**
     * Bulk update settings.
     */
    public function bulkUpdate(Request $request)
    {
        $settings = $request->except(['_token', '_method']);
        
        $updated = $this->settingService->bulkUpdate($settings);
        
        return ApiResponse::success(
            'Settings updated successfully.',
            $updated
        );
    }

    /**
     * Delete a setting.
     */
    public function destroy(string $key)
    {
        $deleted = $this->settingService->delete($key);
        
        if (!$deleted) {
            return ApiResponse::error(
                'Setting not found.',
                Response::HTTP_NOT_FOUND
            );
        }
        
        return ApiResponse::success('Setting deleted successfully.');
    }

    /**
     * Delete a file associated with a setting.
     */
    public function deleteFile(string $key)
    {
        $deleted = $this->settingService->deleteFile($key);
        
        if (!$deleted) {
            return ApiResponse::error(
                'File not found or setting is not a file type.',
                Response::HTTP_NOT_FOUND
            );
        }
        
        return ApiResponse::success('File deleted successfully.');
    }

    /**
     * Get all available setting groups.
     */
    public function groups()
    {
        $groups = $this->settingService->getGroups();
        
        return ApiResponse::success(
            'Setting groups fetched successfully.',
            $groups
        );
    }
}
