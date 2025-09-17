<?php

namespace App\Models;
use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
class CentralUser extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;
    protected $table = 'users';
    protected $connection = 'central';
    protected $guard_name = 'central';
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
     * The attributes that should be hidden for serialization.
    protected $hidden = [
        'remember_token',
     * Get the attributes that should be cast.
     * @return array<string, string>
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'status_updated_at' => 'datetime',
            'status' => UserStatus::class,
            'password' => 'hashed',
        ];
    }
    public function getUserRole(): string
        return $this->roles->pluck('name')->first();
     * Get tenants that this central user manages
    public function managedTenants(): HasMany
        return $this->hasMany(Tenant::class, 'created_by');
}
