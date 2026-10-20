<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\TenantMembership;
use App\Models\User;
use App\Services\AuditService;
use App\Services\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected AuditService $auditService
    ) {}

    public function index(): View
    {
        $tenant = $this->tenantManager->getTenant();
        $this->authorize('manageTeam', $tenant);

        $memberships = TenantMembership::where('tenant_id', $tenant->id)
            ->with('user')
            ->orderByDesc('role')
            ->get();

        return view('team.index', compact('tenant', 'memberships'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();
        $this->authorize('manageTeam', $tenant);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'role' => ['required', 'string', Rule::in(['admin', 'technician'])],
            'password' => ['required', 'string', 'min:8'],
        ]);

        return DB::transaction(function () use ($validated, $tenant) {
            // Find or create user
            $user = User::where('email', $validated['email'])->first();

            if ($user) {
                // Check if already member of this tenant
                $existingMembership = TenantMembership::where('tenant_id', $tenant->id)
                    ->where('user_id', $user->id)
                    ->first();

                if ($existingMembership) {
                    return back()->with('error', "Cet utilisateur fait déjà partie de l'équipe de cet atelier.");
                }
            } else {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'] ?? null,
                    'password' => Hash::make($validated['password']),
                    'is_active' => true,
                ]);
            }

            $membership = TenantMembership::create([
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'role' => $validated['role'],
                'is_active' => true,
            ]);

            $this->auditService->log(
                event: 'team.member_added',
                auditable: $membership,
                newValues: ['email' => $user->email, 'role' => $membership->role],
                userId: auth()->id(),
                tenantId: $tenant->id
            );

            return redirect()->route('team.index')
                ->with('success', "Le collaborateur {$user->name} a été ajouté avec le rôle de " . ($validated['role'] === 'admin' ? 'Administrateur' : 'Technicien') . '.');
        });
    }

    public function update(Request $request, TenantMembership $membership): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();
        $this->authorize('manageTeam', $tenant);

        if ($membership->tenant_id !== $tenant->id) {
            abort(403);
        }

        $validated = $request->validate([
            'role' => ['required', 'string', Rule::in(['admin', 'technician'])],
        ]);

        // Safety rule: Cannot downgrade the last owner
        if ($membership->role === 'owner' && $validated['role'] !== 'owner') {
            $ownerCount = TenantMembership::where('tenant_id', $tenant->id)
                ->where('role', 'owner')
                ->where('is_active', true)
                ->count();

            if ($ownerCount <= 1) {
                return back()->with('error', "Impossible de modifier le rôle du seul propriétaire de l'atelier.");
            }
        }

        $oldRole = $membership->role;
        $membership->update(['role' => $validated['role']]);

        $this->auditService->log(
            event: 'team.member_role_changed',
            auditable: $membership,
            oldValues: ['role' => $oldRole],
            newValues: ['role' => $membership->role],
            userId: auth()->id(),
            tenantId: $tenant->id
        );

        return redirect()->route('team.index')
            ->with('success', "Le rôle du membre {$membership->user->name} a été mis à jour.");
    }

    public function toggleStatus(TenantMembership $membership): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();
        $this->authorize('manageTeam', $tenant);

        if ($membership->tenant_id !== $tenant->id) {
            abort(403);
        }

        // Safety: Cannot deactivate oneself if owner or last owner
        if ($membership->user_id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');
        }

        if ($membership->role === 'owner' && $membership->is_active) {
            $ownerCount = TenantMembership::where('tenant_id', $tenant->id)
                ->where('role', 'owner')
                ->where('is_active', true)
                ->count();

            if ($ownerCount <= 1) {
                return back()->with('error', "Impossible de désactiver le dernier propriétaire de l'atelier.");
            }
        }

        $newStatus = ! $membership->is_active;
        $membership->update(['is_active' => $newStatus]);

        $this->auditService->log(
            event: $newStatus ? 'team.member_activated' : 'team.member_deactivated',
            auditable: $membership,
            newValues: ['is_active' => $newStatus],
            userId: auth()->id(),
            tenantId: $tenant->id
        );

        $msg = $newStatus
            ? "Le compte de {$membership->user->name} a été activé."
            : "Le compte de {$membership->user->name} a été désactivé.";

        return redirect()->route('team.index')->with('success', $msg);
    }
}
