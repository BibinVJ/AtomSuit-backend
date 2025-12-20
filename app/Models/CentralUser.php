<?php

namespace App\Models;
use App\Enums\UserStatus;
use App\Traits\AppAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use MeShaon\RequestAnalytics\Contracts\CanAccessAnalyticsDashboard;
use Spatie\Permission\Traits\HasRoles;

class CentralUser extends Authenticatable implements CanAccessAnalyticsDashboard
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes, AppAudit;

    protected $table = 'users';
    protected $guard_name = 'api';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'status',
        'status_updated_at',
        'phone',
        'phone_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'status_updated_at' => 'datetime',
            'status' => UserStatus::class,
            'password' => 'hashed',
        ];
    }

    public function logindetails(): HasMany
    {
        return $this->hasMany(UserLoginDetail::class);
    }

    /**
     * Get the name of the provider for the model.
     *
     * @return string
     */
    public function getProviderName(): string
    {
        return 'dynamic_users';
    }

    public function canAccessAnalyticsDashboard(): bool
    {
        return $this->hasPermissionTo(\App\Enums\PermissionsEnum::VIEW_SYSTEM_ANALYTICS->value);
    }
}
