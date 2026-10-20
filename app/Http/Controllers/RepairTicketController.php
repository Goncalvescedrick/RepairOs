<?php

namespace App\Http\Controllers;

use App\Enums\RepairStatus;
use App\Enums\TicketPriority;
use App\Models\Customer;
use App\Models\Device;
use App\Models\RepairTicket;
use App\Models\User;
use App\Services\AuditService;
use App\Services\NotificationMessageService;
use App\Services\ReferenceGeneratorService;
use App\Services\RepairStateMachine;
use App\Services\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RepairTicketController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager,
        protected ReferenceGeneratorService $referenceGenerator,
        protected RepairStateMachine $stateMachine,
        protected AuditService $auditService,
        protected NotificationMessageService $notificationService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', RepairTicket::class);

        $query = RepairTicket::query()->with(['customer', 'device', 'assignedTo']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'LIKE', "%{$search}%")
                  ->orWhere('reported_issue', 'LIKE', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'LIKE', "%{$search}%")
                         ->orWhere('phone', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('device', function ($dq) use ($search) {
                      $dq->where('brand', 'LIKE', "%{$search}%")
                         ->orWhere('model', 'LIKE', "%{$search}%")
                         ->orWhere('serial_number', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($priority = $request->input('priority')) {
            $query->where('priority', $priority);
        }

        if ($assignedTo = $request->input('assigned_to')) {
            $query->where('assigned_to_user_id', $assignedTo);
        }

        if ($request->boolean('overdue')) {
            $query->whereNotIn('status', [RepairStatus::RESTITUE->value, RepairStatus::ANNULE->value])
                  ->whereNotNull('estimated_completion_date')
                  ->where('estimated_completion_date', '<', now());
        }

        $tickets = $query->orderByDesc('deposit_date')->paginate(15)->withQueryString();

        $tenantId = $this->tenantManager->getTenantId();
        $technicians = User::whereHas('memberships', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId)->where('is_active', true);
        })->orderBy('name')->get();

        return view('tickets.index', compact('tickets', 'technicians'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', RepairTicket::class);

        $tenantId = $this->tenantManager->getTenantId();
        $customers = Customer::orderBy('name')->get();
        $selectedCustomerId = $request->input('customer_id');

        $devices = collect();
        if ($selectedCustomerId) {
            $devices = Device::where('customer_id', $selectedCustomerId)->get();
        }

        $selectedDeviceId = $request->input('device_id');

        $technicians = User::whereHas('memberships', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId)->where('is_active', true);
        })->orderBy('name')->get();

        return view('tickets.create', compact('customers', 'selectedCustomerId', 'devices', 'selectedDeviceId', 'technicians'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', RepairTicket::class);

        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'device_id' => ['required', 'exists:devices,id'],
            'assigned_to_user_id' => ['nullable', 'exists:users,id'],
            'priority' => ['required', 'string', 'in:BASSE,NORMALE,HAUTE,URGENTE'],
            'reported_issue' => ['required', 'string'],
            'intake_condition' => ['nullable', 'string'],
            'accessories_received' => ['nullable', 'string'],
            'deposit_date' => ['nullable', 'date'],
            'estimated_completion_date' => ['nullable', 'date', 'after_or_equal:deposit_date'],
            'internal_notes' => ['nullable', 'string'],
            'client_notes' => ['nullable', 'string'],
        ]);

        $tenant = $this->tenantManager->getTenant();

        $ticket = DB::transaction(function () use ($tenant, $validated) {
            $reference = $this->referenceGenerator->generateTicketReference($tenant);
            $trackingToken = bin2hex(random_bytes(32));

            $ticket = RepairTicket::create([
                'tenant_id' => $tenant->id,
                'reference' => $reference,
                'customer_id' => $validated['customer_id'],
                'device_id' => $validated['device_id'],
                'assigned_to_user_id' => $validated['assigned_to_user_id'] ?? null,
                'status' => RepairStatus::RECU,
                'priority' => TicketPriority::from($validated['priority']),
                'reported_issue' => $validated['reported_issue'],
                'intake_condition' => $validated['intake_condition'] ?? null,
                'accessories_received' => $validated['accessories_received'] ?? null,
                'deposit_date' => $validated['deposit_date'] ?? now(),
                'estimated_completion_date' => $validated['estimated_completion_date'] ?? null,
                'internal_notes' => $validated['internal_notes'] ?? null,
                'client_notes' => $validated['client_notes'] ?? null,
                'public_tracking_token' => $trackingToken,
            ]);

            // Initial status history
            $ticket->statusHistories()->create([
                'tenant_id' => $tenant->id,
                'user_id' => auth()->id(),
                'from_status' => null,
                'to_status' => RepairStatus::RECU,
                'reason' => 'Réception initiale de l’appareil à l’atelier',
                'created_at' => now(),
            ]);

            $this->auditService->log(
                event: 'ticket.created',
                auditable: $ticket,
                newValues: ['reference' => $ticket->reference, 'customer_id' => $ticket->customer_id],
                tenantId: $tenant->id
            );

            return $ticket;
        });

        return redirect()->route('tickets.show', $ticket)->with('success', "Ticket {$ticket->reference} ouvert avec succès.");
    }

    public function show(RepairTicket $ticket): View
    {
        $this->authorize('view', $ticket);

        $ticket->load([
            'customer',
            'device.attachments',
            'assignedTo',
            'statusHistories.user',
            'interventions.user',
            'quotes.items',
            'invoices.payments.user',
            'attachments.user',
        ]);

        $allowedTransitions = $this->stateMachine->getAllowedTransitions($ticket);

        // Pre-generate WhatsApp message for intake
        $intakeMessage = $this->notificationService->getIntakeMessage($ticket);
        $intakeWhatsAppUrl = $this->notificationService->generateWhatsAppUrl($ticket->customer->phone, $intakeMessage);

        // Pre-generate WhatsApp ready for pickup message
        $latestInvoice = $ticket->invoices->where('status', '!=', 'ANNULEE')->sortByDesc('created_at')->first();
        $readyMessage = $this->notificationService->getReadyForPickupMessage($ticket, $latestInvoice);
        $readyWhatsAppUrl = $this->notificationService->generateWhatsAppUrl($ticket->customer->phone, $readyMessage);

        $tenantId = $this->tenantManager->getTenantId();
        $technicians = User::whereHas('memberships', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId)->where('is_active', true);
        })->orderBy('name')->get();

        return view('tickets.show', compact(
            'ticket',
            'allowedTransitions',
            'intakeMessage',
            'intakeWhatsAppUrl',
            'readyMessage',
            'readyWhatsAppUrl',
            'latestInvoice',
            'technicians'
        ));
    }

    public function edit(RepairTicket $ticket): View
    {
        $this->authorize('update', $ticket);

        $tenantId = $this->tenantManager->getTenantId();
        $technicians = User::whereHas('memberships', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId)->where('is_active', true);
        })->orderBy('name')->get();

        return view('tickets.edit', compact('ticket', 'technicians'));
    }

    public function update(Request $request, RepairTicket $ticket): RedirectResponse
    {
        $this->authorize('update', $ticket);

        $validated = $request->validate([
            'assigned_to_user_id' => ['nullable', 'exists:users,id'],
            'priority' => ['required', 'string', 'in:BASSE,NORMALE,HAUTE,URGENTE'],
            'reported_issue' => ['required', 'string'],
            'intake_condition' => ['nullable', 'string'],
            'accessories_received' => ['nullable', 'string'],
            'estimated_completion_date' => ['nullable', 'date'],
            'internal_notes' => ['nullable', 'string'],
            'client_notes' => ['nullable', 'string'],
        ]);

        $old = $ticket->only(array_keys($validated));
        $ticket->update($validated);

        $this->auditService->log(
            event: 'ticket.updated',
            auditable: $ticket,
            oldValues: $old,
            newValues: $ticket->only(array_keys($validated))
        );

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket mis à jour.');
    }

    public function transition(Request $request, RepairTicket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $targetStatus = RepairStatus::tryFrom($validated['status']);
        if (! $targetStatus) {
            return back()->with('error', 'Statut demandé invalide.');
        }

        $this->authorize('transitionTo', [$ticket, $targetStatus]);

        try {
            $this->stateMachine->transition(
                ticket: $ticket,
                targetStatus: $targetStatus,
                user: auth()->user(),
                reason: $validated['reason'] ?? null
            );

            return redirect()->route('tickets.show', $ticket)
                ->with('success', "Le ticket est passé au statut « {$targetStatus->label()} ».");
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function receipt(RepairTicket $ticket): View
    {
        $this->authorize('view', $ticket);

        $ticket->load(['customer', 'device', 'assignedTo', 'tenant']);

        return view('tickets.receipt', compact('ticket'));
    }

    public function rotateTrackingToken(RepairTicket $ticket): RedirectResponse
    {
        $this->authorize('update', $ticket);

        $oldToken = $ticket->public_tracking_token;
        $newToken = \Illuminate\Support\Str::random(64);

        $ticket->update(['public_tracking_token' => $newToken]);

        $this->auditService->log(
            event: 'ticket.tracking_token_rotated',
            auditable: $ticket,
            oldValues: ['token' => substr($oldToken, 0, 8) . '...'],
            newValues: ['token' => substr($newToken, 0, 8) . '...'],
            userId: auth()->id(),
            tenantId: $ticket->tenant_id
        );

        return back()->with('success', 'Le lien de suivi client a été renouvelé avec succès. L\'ancien lien ne fonctionne plus.');
    }
}
