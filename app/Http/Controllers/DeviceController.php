<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Device;
use App\Services\AuditService;
use App\Services\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DeviceController extends Controller
{
    public function __construct(
        protected AuditService $auditService,
        protected TenantManager $tenantManager
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Device::class);

        $query = Device::query()->with(['customer'])->withCount('repairTickets');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('brand', 'LIKE', "%{$search}%")
                    ->orWhere('model', 'LIKE', "%{$search}%")
                    ->orWhere('serial_number', 'LIKE', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('phone', 'LIKE', "%{$search}%");
                    });
            });
        }

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        $devices = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        return view('devices.index', compact('devices'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Device::class);

        $selectedCustomerId = $request->input('customer_id');
        $customers = Customer::orderBy('name')->get();

        return view('devices.create', compact('customers', 'selectedCustomerId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Device::class);

        $tenantId = $this->tenantManager->getTenantId();

        $validated = $request->validate([
            'customer_id' => [
                'required',
                Rule::exists('customers', 'id')->where('tenant_id', $tenantId),
            ],
            'category' => ['required', 'string', 'in:laptop,desktop,printer,network,other,LAPTOP,DESKTOP,PRINTER,NETWORK,OTHER'],
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'aesthetic_condition' => ['nullable', 'string'],
            'accessories' => ['nullable', 'string'],
            'reported_accessories' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        // Security check: ensure customer belongs to current tenant
        $customer = Customer::findOrFail($validated['customer_id']);

        $device = Device::create([
            'tenant_id' => $tenantId,
            'customer_id' => $customer->id,
            'category' => strtolower($validated['category']),
            'brand' => $validated['brand'],
            'model' => $validated['model'],
            'serial_number' => $validated['serial_number'] ?? null,
            'aesthetic_condition' => $validated['aesthetic_condition'] ?? null,
            'accessories' => $validated['accessories'] ?? $validated['reported_accessories'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        $this->auditService->log(
            event: 'device.created',
            auditable: $device,
            newValues: ['brand' => $device->brand, 'model' => $device->model, 'customer_id' => $device->customer_id]
        );

        return redirect()->route('devices.show', $device)->with('success', 'Appareil enregistré avec succès.');
    }

    public function show(Device $device): View
    {
        $this->authorize('view', $device);

        $device->load([
            'customer',
            'repairTickets.assignedTo',
            'attachments.user',
        ]);

        return view('devices.show', compact('device'));
    }

    public function edit(Device $device): View
    {
        $this->authorize('update', $device);

        $customers = Customer::orderBy('name')->get();

        return view('devices.edit', compact('device', 'customers'));
    }

    public function update(Request $request, Device $device): RedirectResponse
    {
        $this->authorize('update', $device);

        $validated = $request->validate([
            'category' => ['required', 'string', 'in:laptop,desktop,printer,network,other,LAPTOP,DESKTOP,PRINTER,NETWORK,OTHER'],
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'aesthetic_condition' => ['nullable', 'string'],
            'accessories' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['category'] = strtolower($validated['category']);
        $oldValues = $device->only(['category', 'brand', 'model', 'serial_number', 'aesthetic_condition', 'accessories', 'notes']);
        $device->update($validated);

        $this->auditService->log(
            event: 'device.updated',
            auditable: $device,
            oldValues: $oldValues,
            newValues: $device->only(['category', 'brand', 'model', 'serial_number', 'aesthetic_condition', 'accessories', 'notes'])
        );

        return redirect()->route('devices.show', $device)->with('success', 'Fiche appareil mise à jour.');
    }

    public function destroy(Device $device): RedirectResponse
    {
        $this->authorize('delete', $device);

        $openTicketsCount = $device->repairTickets()
            ->whereNotIn('status', ['RESTITUE', 'ANNULE'])
            ->count();

        if ($openTicketsCount > 0) {
            return back()->with('error', "Impossible d'archiver cet appareil : il fait l'objet d'un ticket en cours.");
        }

        $device->delete();

        $this->auditService->log(
            event: 'device.archived',
            auditable: $device,
            newValues: ['brand' => $device->brand, 'model' => $device->model]
        );

        return redirect()->route('devices.index')->with('success', 'Appareil archivé avec succès.');
    }
}
