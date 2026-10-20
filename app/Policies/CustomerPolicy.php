<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\User;
use App\Services\TenantManager;

class CustomerPolicy
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

    public function view(User $user, Customer $customer): bool
    {
        return $customer->tenant_id === app(TenantManager::class)->getTenantId();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Customer $customer): bool
    {
        return $customer->tenant_id === app(TenantManager::class)->getTenantId();
    }

    public function delete(User $user, Customer $customer): bool
    {
        $tenantId = app(TenantManager::class)->getTenantId();

        if ($customer->tenant_id !== $tenantId) {
            return false;
        }

        // Technicians cannot archive customers, only Owners and Admins
        return $user->isAdminOrOwnerIn($tenantId);
    }
}
