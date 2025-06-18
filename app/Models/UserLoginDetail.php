<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLoginDetail extends Model
{
    protected $fillable = [
        'user_id',
        'token_id',
        'login_at',
        'logout_at',
        'ip_address',
        'user_agent',
        'login_method',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sessionDuration(): int
    {
        if ($this->logout_at) {
            return $this->logout_at->diffInSeconds($this->login_at);
        }
        return now()->diffInSeconds($this->login_at);
    }

    // Scope for active sessions
    public function scopeActive($query)
    {
        return $query->whereNull('logout_at');
    }

    // Scope for specific user
    // public function scopeForUser($query, $userId)
    // {
    //     return $query->where('user_id', $userId);
    // }
}
