<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\AuditService;
use App\Services\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkshopSettingsController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected AuditService $auditService
    ) {}

    public function index(): View
    {
        $tenant = $this->tenantManager->getTenant();
        $this->authorize('update', $tenant);

        return view('settings.index', compact('tenant'));
    }

    public function update(Request $request): RedirectResponse
    {
        $tenant = $this->tenantManager->getTenant();
        $this->authorize('update', $tenant);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'country_code' => ['required', 'string', 'size:2'],
            'currency' => ['required', 'string', 'max:10'],
            'timezone' => ['required', 'string', 'max:50'],
            'ticket_prefix' => ['required', 'string', 'max:10', 'alpha_num'],
            'quote_prefix' => ['required', 'string', 'max:10', 'alpha_num'],
            'invoice_prefix' => ['required', 'string', 'max:10', 'alpha_num'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'legal_notices' => ['nullable', 'string', 'max:1000'],
        ]);

        $oldValues = $tenant->only(array_keys($validated));
        $tenant->update($validated);

        $this->auditService->log(
            event: 'workshop.settings_updated',
            auditable: $tenant,
            oldValues: $oldValues,
            newValues: $tenant->only(array_keys($validated)),
            userId: auth()->id(),
            tenantId: $tenant->id
        );

        return redirect()->route('settings.index')->with('success', 'Paramètres de l\'atelier enregistrés avec succès.');
    }
}
