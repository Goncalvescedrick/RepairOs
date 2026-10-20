<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use App\Services\TenantManager;

class InvoicePolicy
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

    public function view(User $user, Invoice $invoice): bool
    {
        $tenantId = app(TenantManager::class)->getTenantId();

        if ($invoice->tenant_id !== $tenantId) {
            return false;
        }

        if ($user->isAdminOrOwnerIn($tenantId)) {
            return true;
        }

        // Technician can view invoice if assigned to the repair ticket
        return $invoice->ticket?->assigned_to_user_id === $user->id;
    }

    public function create(User $user): bool
    {
        $tenantId = app(TenantManager::class)->getTenantId();

        return $user->isAdminOrOwnerIn($tenantId);
    }

    public function cancel(User $user, Invoice $invoice): bool
    {
        $tenantId = app(TenantManager::class)->getTenantId();

        return $invoice->tenant_id === $tenantId && $user->isAdminOrOwnerIn($tenantId);
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        // Invoices cannot be silently deleted
        return false;
    }
}
