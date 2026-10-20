<?php

namespace App\Http\Middleware;

use App\Services\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRolePermission
{
    public function __construct(
        protected TenantManager $tenantManager
    ) {}

    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::user();
        $tenant = $this->tenantManager->getTenant();

        if (! $user || ! $tenant) {
            abort(403, 'Accès non autorisé : aucun atelier actif sélectionné.');
        }

        $userRole = $user->roleInTenant($tenant->id);

        if (! $userRole || ! in_array($userRole->value, $roles, true)) {
            abort(403, 'Accès refusé : vous ne possédez pas les permissions nécessaires pour effectuer cette action.');
        }

        return $next($request);
    }
}
