<?php

namespace App\Policies;

use App\Models\Quote;
use App\Models\User;
use App\Services\TenantManager;

class QuotePolicy
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

    public function view(User $user, Quote $quote): bool
    {
        return $quote->tenant_id === app(TenantManager::class)->getTenantId();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Quote $quote): bool
    {
        $tenantId = app(TenantManager::class)->getTenantId();

        return $quote->tenant_id === $tenantId && $quote->status->isEditable();
    }

    public function acceptOrRefuse(User $user, Quote $quote): bool
    {
        return $quote->tenant_id === app(TenantManager::class)->getTenantId();
    }

    public function delete(User $user, Quote $quote): bool
    {
        $tenantId = app(TenantManager::class)->getTenantId();

        return $quote->tenant_id === $tenantId && $user->isAdminOrOwnerIn($tenantId);
    }
}
