<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\User;
use App\Services\TenantManager;

class AttachmentPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        $tenantId = app(TenantManager::class)->getTenantId();

        if (! $tenantId || ! $user->membershipInTenant($tenantId)?->is_active) {
            return false;
        }

        return null;
    }

    public function view(User $user, Attachment $attachment): bool
    {
        return $attachment->tenant_id === app(TenantManager::class)->getTenantId();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function delete(User $user, Attachment $attachment): bool
    {
        $tenantId = app(TenantManager::class)->getTenantId();

        if ($attachment->tenant_id !== $tenantId) {
            return false;
        }

        return $user->isAdminOrOwnerIn($tenantId) || $attachment->user_id === $user->id;
    }
}
