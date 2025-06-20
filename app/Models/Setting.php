<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function get($key, $default = null)
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    public static function set($key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
