<?php

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantManager;

class TenantPolicy
{
    public function view(User $user, Tenant $tenant): bool
    {
        return $tenant->id === app(TenantManager::class)->getTenantId();
    }

    public function update(User $user, Tenant $tenant): bool
    {
        return $tenant->id === app(TenantManager::class)->getTenantId() && $user->isOwnerOf($tenant->id);
    }

    public function manageTeam(User $user, Tenant $tenant): bool
    {
        return $tenant->id === app(TenantManager::class)->getTenantId() && $user->isOwnerOf($tenant->id);
    }

    public function viewReports(User $user, Tenant $tenant): bool
    {
        return $tenant->id === app(TenantManager::class)->getTenantId() && $user->isAdminOrOwnerIn($tenant->id);
    }
}
