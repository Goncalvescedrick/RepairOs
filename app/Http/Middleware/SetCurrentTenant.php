<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Models\TenantMembership;
use App\Services\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentTenant
{
    public function __construct(
        protected TenantManager $tenantManager
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            if (! $user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'Votre compte utilisateur a été désactivé. Veuillez contacter votre administrateur.',
                ]);
            }

            $currentTenantId = $request->session()->get('current_tenant_id');
            $tenant = null;

            if ($currentTenantId) {
                $membership = TenantMembership::where('user_id', $user->id)
                    ->where('tenant_id', $currentTenantId)
                    ->where('is_active', true)
                    ->first();

                if ($membership) {
                    $tenant = Tenant::find($currentTenantId);
                }
            }

            if (! $tenant) {
                $firstMembership = TenantMembership::where('user_id', $user->id)
                    ->where('is_active', true)
                    ->first();

                if ($firstMembership) {
                    $tenant = $firstMembership->tenant;
                    $request->session()->put('current_tenant_id', $tenant->id);
                }
            }

            if ($tenant && $tenant->is_active) {
                $this->tenantManager->setTenant($tenant);
            } elseif (! $request->routeIs('logout')) {
                if (\Illuminate\Support\Facades\Route::has('onboarding.create')) {
                    return redirect()->route('onboarding.create');
                }
                Auth::logout();
                return redirect()->route('login')->with('error', "Aucun atelier actif n'est associé à votre compte.");
            }
        }

        return $next($request);
    }
}
