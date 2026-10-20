<?php

namespace App\Services;

use App\Enums\RepairStatus;
use App\Models\RepairStatusHistory;
use App\Models\RepairTicket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RepairStateMachine
{
    /**
     * Define authorized transitions mapping.
     */
    protected array $transitions = [
        RepairStatus::RECU->value => [
            RepairStatus::DIAGNOSTIC,
            RepairStatus::ANNULE,
        ],
        RepairStatus::DIAGNOSTIC->value => [
            RepairStatus::DEVIS_EN_ATTENTE,
            RepairStatus::EN_REPARATION,
            RepairStatus::ANNULE,
        ],
        RepairStatus::DEVIS_EN_ATTENTE->value => [
            RepairStatus::EN_ATTENTE_CLIENT,
            RepairStatus::EN_REPARATION,
            RepairStatus::ANNULE,
        ],
        RepairStatus::EN_ATTENTE_CLIENT->value => [
            RepairStatus::EN_REPARATION,
            RepairStatus::PRET_A_RETIRER,
            RepairStatus::ANNULE,
        ],
        RepairStatus::EN_REPARATION->value => [
            RepairStatus::TEST,
            RepairStatus::PRET_A_RETIRER,
            RepairStatus::ANNULE,
        ],
        RepairStatus::TEST->value => [
            RepairStatus::EN_REPARATION,
            RepairStatus::PRET_A_RETIRER,
            RepairStatus::ANNULE,
        ],
        RepairStatus::PRET_A_RETIRER->value => [
            RepairStatus::RESTITUE,
            RepairStatus::ANNULE,
        ],
        RepairStatus::RESTITUE->value => [],
        RepairStatus::ANNULE->value => [],
    ];

    /**
     * Check if a transition is valid.
     */
    public function canTransition(RepairTicket $ticket, RepairStatus $targetStatus): bool
    {
        $current = $ticket->status instanceof RepairStatus ? $ticket->status : RepairStatus::from($ticket->status);

        if ($current === $targetStatus) {
            return false;
        }

        $allowed = $this->transitions[$current->value] ?? [];

        return in_array($targetStatus, $allowed, true);
    }

    /**
     * Get list of allowed target statuses for a ticket.
     *
     * @return array<RepairStatus>
     */
    public function getAllowedTransitions(RepairTicket $ticket): array
    {
        $current = $ticket->status instanceof RepairStatus ? $ticket->status : RepairStatus::from($ticket->status);

        return $this->transitions[$current->value] ?? [];
    }

    /**
     * Execute a status transition inside a safe database transaction.
     */
    public function transition(
        RepairTicket $ticket,
        RepairStatus $targetStatus,
        ?User $user = null,
        ?string $reason = null,
        bool $bypassRestitutionCheck = false
    ): RepairTicket {
        if (! $this->canTransition($ticket, $targetStatus)) {
            $fromLabel = $ticket->status instanceof RepairStatus ? $ticket->status->label() : $ticket->status;
            throw new InvalidArgumentException(
                "La transition de « {$fromLabel} » vers « {$targetStatus->label()} » n'est pas autorisée."
            );
        }

        // Guard: Cancellation requires an explicit reason
        if ($targetStatus === RepairStatus::ANNULE && empty(trim((string) $reason))) {
            throw new InvalidArgumentException(
                'Un motif explicite est obligatoire pour annuler un ticket de réparation.'
            );
        }

        // Guard: Restitution verification
        if ($targetStatus === RepairStatus::RESTITUE && ! $bypassRestitutionCheck) {
            $unpaidInvoice = $ticket->invoices()
                ->where('balance_due', '>', 0)
                ->where('status', '!=', 'ANNULEE')
                ->first();

            if ($unpaidInvoice) {
                throw new InvalidArgumentException(
                    "Impossible de restituer l'appareil : la facture {$unpaidInvoice->reference} présente un solde restant dû de {$unpaidInvoice->balance_due} {$unpaidInvoice->currency}."
                );
            }
        }

        return DB::transaction(function () use ($ticket, $targetStatus, $user, $reason) {
            $oldStatus = $ticket->status;

            $ticket->status = $targetStatus;

            if ($targetStatus->isTerminal()) {
                $ticket->closed_at = now();
            }

            $ticket->save();

            // Record status history
            RepairStatusHistory::create([
                'tenant_id' => $ticket->tenant_id,
                'repair_ticket_id' => $ticket->id,
                'user_id' => $user?->id,
                'from_status' => $oldStatus instanceof RepairStatus ? $oldStatus->value : $oldStatus,
                'to_status' => $targetStatus->value,
                'reason' => $reason,
                'created_at' => now(),
            ]);

            // Audit
            app(AuditService::class)->log(
                event: 'ticket.status_changed',
                auditable: $ticket,
                oldValues: ['status' => $oldStatus instanceof RepairStatus ? $oldStatus->value : $oldStatus],
                newValues: ['status' => $targetStatus->value, 'reason' => $reason],
                userId: $user?->id,
                tenantId: $ticket->tenant_id
            );

            return $ticket;
        });
    }
}
