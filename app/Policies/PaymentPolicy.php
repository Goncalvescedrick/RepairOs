<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use App\Services\TenantManager;

class PaymentPolicy
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
        $tenantId = app(TenantManager::class)->getTenantId();

        return $user->isAdminOrOwnerIn($tenantId);
    }

    public function view(User $user, Payment $payment): bool
    {
        $tenantId = app(TenantManager::class)->getTenantId();

        return $payment->tenant_id === $tenantId;
    }

    public function create(User $user): bool
    {
        $tenantId = app(TenantManager::class)->getTenantId();

        return $user->isAdminOrOwnerIn($tenantId);
    }

    public function cancel(User $user, Payment $payment): bool
    {
        $tenantId = app(TenantManager::class)->getTenantId();

        // Only OWNER can cancel an already recorded payment with an audit trail
        return $payment->tenant_id === $tenantId && $user->isOwnerOf($tenantId);
    }

    public function delete(User $user, Payment $payment): bool
    {
        // Payments must never be deleted physically
        return false;
    }
}
