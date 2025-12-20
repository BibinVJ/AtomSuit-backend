<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'description',
    ];

    /**
     * Get the value attribute and cast it based on type.
     */
    public function getValueAttribute($value)
    {
        return match ($this->type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'json' => json_decode($value, true) ?? $value,
            'file' => $value, // Return path as-is
            default => $value, // string
        };
    }

    /**
     * Set the value attribute and encode if needed.
     */
    public function setValueAttribute($value)
    {
        $this->attributes['value'] = match ($this->type ?? 'string') {
            'boolean' => $value ? '1' : '0',
            'json' => is_array($value) || is_object($value) ? json_encode($value) : $value,
            default => $value,
        };
    }

    /**
     * Get a setting value by key.
     */
    public static function getValue(string $key, $default = null)
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set a setting value by key.
     */
    public static function setValue(string $key, $value, ?string $type = null, ?string $group = null): void
    {
        static::updateOrCreate(
            ['key' => $key],
            array_filter([
                'value' => $value,
                'type' => $type,
                'group' => $group,
            ], fn($v) => !is_null($v))
        );
        
        Cache::forget("setting.{$key}");
    }

    /**
     * Clear the settings cache.
     */
    public static function clearCache(): void
    {
        Cache::flush();
    }
}
