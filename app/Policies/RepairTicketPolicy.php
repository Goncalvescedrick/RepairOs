<?php

namespace App\Policies;

use App\Enums\RepairStatus;
use App\Models\RepairTicket;
use App\Models\User;
use App\Services\TenantManager;

class RepairTicketPolicy
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

    public function view(User $user, RepairTicket $ticket): bool
    {
        return $ticket->tenant_id === app(TenantManager::class)->getTenantId();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, RepairTicket $ticket): bool
    {
        return $ticket->tenant_id === app(TenantManager::class)->getTenantId();
    }

    public function changePriorityOrAssignee(User $user, RepairTicket $ticket): bool
    {
        $tenantId = app(TenantManager::class)->getTenantId();

        return $ticket->tenant_id === $tenantId && $user->isAdminOrOwnerIn($tenantId);
    }

    public function transitionTo(User $user, RepairTicket $ticket, RepairStatus $targetStatus): bool
    {
        $tenantId = app(TenantManager::class)->getTenantId();

        if ($ticket->tenant_id !== $tenantId) {
            return false;
        }

        // Restitution and Cancellation require Admin or Owner
        if (in_array($targetStatus, [RepairStatus::RESTITUE, RepairStatus::ANNULE], true)) {
            return $user->isAdminOrOwnerIn($tenantId);
        }

        return true;
    }

    public function delete(User $user, RepairTicket $ticket): bool
    {
        $tenantId = app(TenantManager::class)->getTenantId();

        return $ticket->tenant_id === $tenantId && $user->isOwnerOf($tenantId);
    }
}
