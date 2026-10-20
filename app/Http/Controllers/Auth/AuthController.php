<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantMembership;
use App\Models\User;
use App\Services\AuditService;
use App\Services\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        protected AuditService $auditService,
        protected TenantManager $tenantManager
    ) {}

    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => "Trop de tentatives de connexion. Veuillez réessayer dans {$seconds} secondes.",
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            $user = Auth::user();

            if (! $user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'Votre compte est inactif ou désactivé.',
                ]);
            }

            // Select active tenant
            $membership = $user->memberships()->where('is_active', true)->first();

            if ($membership) {
                $request->session()->put('current_tenant_id', $membership->tenant_id);
                $this->tenantManager->setTenant($membership->tenant);
            }

            $this->auditService->log(
                event: 'auth.login_success',
                userId: $user->id,
                tenantId: $membership?->tenant_id
            );

            return redirect()->intended(route('dashboard'));
        }

        RateLimiter::hit($throttleKey, 60);

        $this->auditService->log(
            event: 'auth.login_failed',
            newValues: ['email' => $request->input('email')]
        );

        return back()->withErrors([
            'email' => 'Les identifiants fournis ne correspondent à aucun compte actif.',
        ])->onlyInput('email');
    }

    public function showRegisterForm(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:190', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:50'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'workshop_name' => ['required', 'string', 'max:150'],
            'city' => ['nullable', 'string', 'max:100'],
            'currency' => ['nullable', 'string', 'max:10'],
        ]);

        $user = DB::transaction(function () use ($validated, $request) {
            // 1. Create User
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'is_active' => true,
            ]);

            // 2. Generate unique slug for the workshop
            $baseSlug = Str::slug($validated['workshop_name']);
            $slug = $baseSlug ?: 'atelier';
            $count = Tenant::where('slug', 'LIKE', "{$slug}%")->count();
            if ($count > 0) {
                $slug .= '-' . ($count + 1);
            }

            // 3. Create Tenant
            $tenant = Tenant::create([
                'name' => $validated['workshop_name'],
                'slug' => $slug,
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'city' => $validated['city'] ?? 'Ouagadougou',
                'country_code' => 'BF',
                'currency' => $validated['currency'] ?: 'XOF',
                'timezone' => 'Africa/Ouagadougou',
                'ticket_prefix' => 'TICK',
                'quote_prefix' => 'DEV',
                'invoice_prefix' => 'FAC',
                'tax_rate' => 0.00,
                'is_active' => true,
            ]);

            // 4. Create Owner Membership
            TenantMembership::create([
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'role' => UserRole::OWNER->value,
                'is_active' => true,
            ]);

            $this->auditService->log(
                event: 'tenant.registered',
                auditable: $tenant,
                newValues: ['tenant_name' => $tenant->name, 'owner_email' => $user->email],
                userId: $user->id,
                tenantId: $tenant->id
            );

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        $membership = $user->memberships()->first();
        if ($membership) {
            $request->session()->put('current_tenant_id', $membership->tenant_id);
            $this->tenantManager->setTenant($membership->tenant);
        }

        return redirect()->route('dashboard')->with('success', 'Bienvenue sur RepairOS ! Votre atelier a été configuré avec succès.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $userId = Auth::id();
        $tenantId = $this->tenantManager->getTenantId();

        $this->auditService->log(
            event: 'auth.logout',
            userId: $userId,
            tenantId: $tenantId
        );

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Vous avez été déconnecté avec succès.');
    }
}
