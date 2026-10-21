<?php

namespace App\Http\Controllers;

use App\Models\RepairIntervention;
use App\Models\RepairTicket;
use App\Services\AuditService;
use App\Services\TenantManager;
use Illuminate\Http\JsonResponse;
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

    /**
     * Copilot Atelier : transforme des notes techniques brutes en synthèse claire et rassurante pour le client.
     */
    public function generateClientSummary(Request $request, RepairTicket $ticket): JsonResponse
    {
        $this->authorize('view', $ticket);

        $validated = $request->validate([
            'intervention_type' => ['nullable', 'string'],
            'work_performed' => ['nullable', 'string', 'max:2000'],
            'diagnosis' => ['nullable', 'string', 'max:2000'],
            'test_results' => ['nullable', 'string', 'max:1000'],
        ]);

        $type = $validated['intervention_type'] ?? 'DIAGNOSTIC';
        $work = trim((string) ($validated['work_performed'] ?? ''));
        $diag = trim((string) ($validated['diagnosis'] ?? ''));
        $tests = trim((string) ($validated['test_results'] ?? ''));

        $deviceLabel = trim($ticket->device->brand.' '.$ticket->device->model);

        $typePrefix = match ($type) {
            'DIAGNOSTIC' => "Examen technique complet effectué sur votre {$deviceLabel}.",
            'OPERATION' => "Intervention de réparation réalisée sur votre {$deviceLabel}.",
            'TEST' => "Contrôle qualité et tests de stabilité effectués sur votre {$deviceLabel}.",
            default => "Point d'étape technique sur votre {$deviceLabel}.",
        };

        $parts = [$typePrefix];

        if ($diag !== '') {
            $cleanDiag = rtrim($diag, '.');
            $parts[] = "Diagnostic identifié : {$cleanDiag}.";
        }

        if ($work !== '') {
            $cleanWork = rtrim($work, '.');
            $parts[] = "Opérations réalisées : {$cleanWork}.";
        }

        if ($tests !== '') {
            $cleanTests = rtrim($tests, '.');
            $parts[] = "Tests de validation : {$cleanTests}.";
        }

        return response()->json([
            'summary' => implode(' ', $parts),
        ]);
    }
}
