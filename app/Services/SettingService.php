<?php

namespace App\Services;

use App\Models\Setting;
use App\Repositories\SettingRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class SettingService
{
    public function __construct(protected SettingRepository $settingRepository) {}

    /**
     * Get a setting value by key.
     */
    public function get(string $key, $default = null)
    {
        return Setting::getValue($key, $default);
    }

    /**
     * Set a setting value.
     */
    public function set(string $key, $value, ?string $type = null, ?string $group = null): Setting
    {
        Setting::setValue($key, $value, $type, $group);
        return Setting::where('key', $key)->first();
    }

    /**
     * Get all settings grouped by category.
     */
    public function getAllGrouped(): array
    {
        return $this->settingRepository->getAllGrouped();
    }

    /**
     * Get all settings for a specific group.
     */
    public function getByGroup(string $group): array
    {
        return $this->settingRepository->list(['group' => $group]);
    }

    /**
     * Bulk update settings.
     */
    public function bulkUpdate(array $settings): array
    {
        $updated = [];

        foreach ($settings as $key => $value) {
            try {
                // Check if it's a file upload
                if ($value instanceof UploadedFile) {
                    $value = $this->handleFileUpload($key, $value);
                }

                $setting = Setting::where('key', $key)->first();
                
                if ($setting) {
                    $setting->value = $value;
                    $setting->save();
                    $updated[$key] = $setting->value;
                } else {
                    // Create new setting if doesn't exist
                    Setting::setValue($key, $value);
                    $updated[$key] = $value;
                }

                Setting::clearCache();
            } catch (\Exception $e) {
                Log::error("Failed to update setting: {$key}", [
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $updated;
    }

    /**
     * Handle file upload for settings like logo, favicon, etc.
     */
    public function handleFileUpload(string $key, UploadedFile $file): string
    {
        // Delete old file if exists
        $oldSetting = Setting::where('key', $key)->first();
        if ($oldSetting && $oldSetting->value) {
            Storage::disk('public')->delete($oldSetting->value);
        }

        // Store new file
        $path = $file->store('settings', 'public');
        
        Log::info("File uploaded for setting: {$key}", ['path' => $path]);
        
        return $path;
    }

    /**
     * Delete a file associated with a setting.
     */
    public function deleteFile(string $key): bool
    {
        $setting = Setting::where('key', $key)->first();
        
        if ($setting && $setting->type === 'file' && $setting->value) {
            if (Storage::disk('public')->exists($setting->value)) {
                Storage::disk('public')->delete($setting->value);
            }
            
            $setting->value = null;
            $setting->save();
            Setting::clearCache();
            
            return true;
        }
        
        return false;
    }

    /**
     * Get multiple settings by keys.
     */
    public function getMultiple(array $keys): array
    {
        $results = [];
        
        foreach ($keys as $key) {
            $results[$key] = $this->get($key);
        }
        
        return $results;
    }

    /**
     * Check if a setting exists.
     */
    public function exists(string $key): bool
    {
        return Setting::where('key', $key)->exists();
    }

    /**
     * Delete a setting.
     */
    public function delete(string $key): bool
    {
        $setting = Setting::where('key', $key)->first();
        
        if ($setting) {
            // Delete file if it's a file type
            if ($setting->type === 'file' && $setting->value) {
                $this->deleteFile($key);
            }
            
            $setting->delete();
            Setting::clearCache();
            
            return true;
        }
        
        return false;
    }

    /**
     * Get all available setting groups.
     */
    public function getGroups(): array
    {
        return Setting::select('group')
            ->distinct()
            ->orderBy('group')
            ->pluck('group')
            ->toArray();
    }
}
