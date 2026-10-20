<?php

namespace App\Policies;

use App\Models\Device;
use App\Models\User;
use App\Services\TenantManager;

class DevicePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        $tenantId = app(TenantManager::class)->getTenantId();

        if (! $tenantId || ! $user->membershipInTenant($tenantId)?->is_active) {
            return false;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Device $device): bool
    {
        return $device->tenant_id === app(TenantManager::class)->getTenantId();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Device $device): bool
    {
        return $device->tenant_id === app(TenantManager::class)->getTenantId();
    }

    public function delete(User $user, Device $device): bool
    {
        $tenantId = app(TenantManager::class)->getTenantId();

        return $device->tenant_id === $tenantId && $user->isAdminOrOwnerIn($tenantId);
    }
}
