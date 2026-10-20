<?php

namespace App\Http\Controllers;

use App\Models\RepairIntervention;
use App\Models\RepairTicket;
use App\Services\AuditService;
use App\Services\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RepairInterventionController extends Controller
{
    public function __construct(
        protected AuditService $auditService,
        protected TenantManager $tenantManager
    ) {}

    public function store(Request $request, RepairTicket $ticket): RedirectResponse
    {
        $this->authorize('view', $ticket);

        $validated = $request->validate([
            'intervention_type' => ['required', 'string', 'in:DIAGNOSTIC,OPERATION,TEST,AUTRE'],
            'work_performed' => ['nullable', 'string'],
            'symptoms_observed' => ['nullable', 'string'],
            'diagnosis' => ['nullable', 'string'],
            'diagnostic' => ['nullable', 'string'],
            'identified_cause' => ['nullable', 'string'],
            'test_results' => ['nullable', 'string'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'internal_notes' => ['nullable', 'string'],
            'customer_summary' => ['nullable', 'string'],
            'client_report' => ['nullable', 'string'],
        ]);

        $intervention = RepairIntervention::create([
            'tenant_id' => $ticket->tenant_id,
            'repair_ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'intervention_type' => $validated['intervention_type'],
            'work_performed' => $validated['work_performed'] ?? $validated['diagnostic'] ?? $validated['diagnosis'] ?? 'Intervention technique',
            'symptoms_observed' => $validated['symptoms_observed'] ?? null,
            'diagnosis' => $validated['diagnosis'] ?? $validated['diagnostic'] ?? null,
            'identified_cause' => $validated['identified_cause'] ?? null,
            'test_results' => $validated['test_results'] ?? null,
            'duration_minutes' => $validated['duration_minutes'] ?? null,
            'internal_notes' => $validated['internal_notes'] ?? null,
            'customer_summary' => $validated['customer_summary'] ?? $validated['client_report'] ?? null,
        ]);

        $this->auditService->log(
            event: 'intervention.recorded',
            auditable: $intervention,
            newValues: ['type' => $intervention->intervention_type, 'ticket' => $ticket->reference]
        );

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Intervention / Diagnostic consigné avec succès.');
    }
}
