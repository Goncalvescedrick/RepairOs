<?php

namespace App\Policies;

use App\Models\SparePart;
use App\Models\User;
use App\Services\TenantManager;

class SparePartPolicy
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

    public function view(User $user, SparePart $sparePart): bool
    {
        return $sparePart->tenant_id === app(TenantManager::class)->getTenantId();
    }

    public function create(User $user): bool
    {
        $tenantId = app(TenantManager::class)->getTenantId();

        return $user->isAdminOrOwnerIn($tenantId);
    }

    public function update(User $user, SparePart $sparePart): bool
    {
        $tenantId = app(TenantManager::class)->getTenantId();

        return $sparePart->tenant_id === $tenantId && $user->isAdminOrOwnerIn($tenantId);
    }

    public function delete(User $user, SparePart $sparePart): bool
    {
        $tenantId = app(TenantManager::class)->getTenantId();

        return $sparePart->tenant_id === $tenantId && $user->isAdminOrOwnerIn($tenantId);
    }
}
