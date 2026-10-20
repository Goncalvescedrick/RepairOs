<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\AuditService;
use App\Services\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(
        protected AuditService $auditService,
        protected TenantManager $tenantManager
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Customer::class);

        $query = Customer::query()->withCount(['devices', 'repairTickets']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        $customers = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function create(): View
    {
        $this->authorize('create', Customer::class);

        return view('customers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Customer::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:190'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string'],
        ]);

        $tenantId = $this->tenantManager->getTenantId();

        // Check duplicate within tenant
        $exists = Customer::where('tenant_id', $tenantId)
            ->where('phone', $validated['phone'])
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'phone' => 'Un client avec ce nom et ce numéro de téléphone existe déjà dans votre atelier.',
            ]);
        }

        $customer = Customer::create([
            'tenant_id' => $tenantId,
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        $this->auditService->log(
            event: 'customer.created',
            auditable: $customer,
            newValues: ['name' => $customer->name, 'phone' => $customer->phone]
        );

        return redirect()->route('customers.show', $customer)->with('success', 'Fiche client créée avec succès.');
    }

    public function show(Customer $customer): View
    {
        $this->authorize('view', $customer);

        $customer->load([
            'devices',
            'repairTickets.device',
            'repairTickets.invoices',
            'invoices.payments',
        ]);

        $totalInvoiced = $customer->invoices->where('status', '!=', 'ANNULEE')->sum('total_amount');
        $totalPaid = $customer->invoices->where('status', '!=', 'ANNULEE')->sum('paid_amount');
        $totalDue = $customer->invoices->where('status', '!=', 'ANNULEE')->sum('balance_due');

        return view('customers.show', compact('customer', 'totalInvoiced', 'totalPaid', 'totalDue'));
    }

    public function edit(Customer $customer): View
    {
        $this->authorize('update', $customer);

        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $this->authorize('update', $customer);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:190'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string'],
        ]);

        $oldValues = $customer->only(['name', 'phone', 'email', 'address', 'notes']);
        $customer->update($validated);

        $this->auditService->log(
            event: 'customer.updated',
            auditable: $customer,
            oldValues: $oldValues,
            newValues: $customer->only(['name', 'phone', 'email', 'address', 'notes'])
        );

        return redirect()->route('customers.show', $customer)->with('success', 'Client mis à jour avec succès.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $this->authorize('delete', $customer);

        // Rule: Avoid deleting if open repair tickets exist
        $openTicketsCount = $customer->repairTickets()
            ->whereNotIn('status', ['RESTITUE', 'ANNULE'])
            ->count();

        if ($openTicketsCount > 0) {
            return back()->with('error', "Impossible d'archiver ce client : il possède encore {$openTicketsCount} réparation(s) en cours.");
        }

        $customer->delete();

        $this->auditService->log(
            event: 'customer.archived',
            auditable: $customer,
            newValues: ['name' => $customer->name]
        );

        return redirect()->route('customers.index')->with('success', 'Client archivé avec succès.');
    }
}
