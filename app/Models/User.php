<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(TenantMembership::class);
    }

    public function membershipInTenant(int $tenantId): ?TenantMembership
    {
        return $this->memberships()->where('tenant_id', $tenantId)->first();
    }

    public function roleInTenant(int $tenantId): ?UserRole
    {
        $membership = $this->membershipInTenant($tenantId);

        if (! $membership || ! $membership->is_active) {
            return null;
        }

        return UserRole::tryFrom($membership->role);
    }

    public function isOwnerOf(int $tenantId): bool
    {
        return $this->roleInTenant($tenantId) === UserRole::OWNER;
    }

    public function isAdminOrOwnerIn(int $tenantId): bool
    {
        $role = $this->roleInTenant($tenantId);

        return $role === UserRole::OWNER || $role === UserRole::ADMIN;
    }
}
