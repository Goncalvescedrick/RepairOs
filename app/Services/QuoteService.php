<?php

namespace App\Services;

use App\Enums\QuoteStatus;
use App\Enums\RepairStatus;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\RepairTicket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class QuoteService
{
    public function __construct(
        protected ReferenceGeneratorService $referenceGenerator,
        protected AuditService $auditService,
        protected RepairStateMachine $stateMachine
    ) {}

    public function createQuote(RepairTicket $ticket, array $data, array $items, ?User $user = null): Quote
    {
        $tenant = $ticket->tenant;
        $reference = $this->referenceGenerator->generateQuoteReference($tenant);

        return DB::transaction(function () use ($ticket, $tenant, $reference, $data, $items, $user) {
            $taxRate = isset($data['tax_rate']) ? (float) $data['tax_rate'] : (float) $tenant->tax_rate;
            $discount = isset($data['discount_amount']) ? (float) $data['discount_amount'] : 0.00;

            $subtotal = 0.00;
            $parsedItems = [];

            foreach ($items as $item) {
                $qty = max(0.01, (float) ($item['quantity'] ?? 1.0));
                $unitPrice = max(0.0, (float) ($item['unit_price'] ?? 0.0));
                $lineTotal = round($qty * $unitPrice, 2);
                $subtotal += $lineTotal;

                $parsedItems[] = [
                    'type' => $item['type'] ?? 'SERVICE',
                    'description' => trim((string) ($item['description'] ?? 'Prestation')),
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'total_price' => $lineTotal,
                ];
            }

            $taxAmount = round($subtotal * ($taxRate / 100), 2);
            $totalAmount = max(0.00, round($subtotal - $discount + $taxAmount, 2));

            $quote = Quote::create([
                'tenant_id' => $tenant->id,
                'repair_ticket_id' => $ticket->id,
                'customer_id' => $ticket->customer_id,
                'reference' => $reference,
                'status' => QuoteStatus::BROUILLON,
                'subtotal_amount' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discount,
                'total_amount' => $totalAmount,
                'currency' => $tenant->currency ?: 'XOF',
                'valid_until' => $data['valid_until'] ?? now()->addDays(15),
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($parsedItems as $itemData) {
                $quote->items()->create($itemData);
            }

            // Transition ticket to DEVIS_EN_ATTENTE if in DIAGNOSTIC
            if ($ticket->status === RepairStatus::DIAGNOSTIC && $this->stateMachine->canTransition($ticket, RepairStatus::DEVIS_EN_ATTENTE)) {
                $this->stateMachine->transition($ticket, RepairStatus::DEVIS_EN_ATTENTE, $user, 'Création du devis ' . $quote->reference);
            }

            $this->auditService->log(
                event: 'quote.created',
                auditable: $quote,
                newValues: ['reference' => $quote->reference, 'total_amount' => $quote->total_amount],
                userId: $user?->id,
                tenantId: $tenant->id
            );

            return $quote->load('items');
        });
    }

    public function sendQuote(Quote $quote, ?User $user = null): Quote
    {
        return $this->markAsSent($quote, $user);
    }

    public function markAsSent(Quote $quote, ?User $user = null): Quote
    {
        if ($quote->status !== QuoteStatus::BROUILLON) {
            throw new InvalidArgumentException("Seul un devis au statut Brouillon peut être marqué comme envoyé.");
        }

        return DB::transaction(function () use ($quote, $user) {
            $quote->status = QuoteStatus::ENVOYE;
            $quote->sent_at = now();
            $quote->save();

            $ticket = $quote->ticket;
            if ($ticket && $this->stateMachine->canTransition($ticket, RepairStatus::EN_ATTENTE_CLIENT)) {
                $this->stateMachine->transition($ticket, RepairStatus::EN_ATTENTE_CLIENT, $user, "Devis {$quote->reference} transmis au client");
            }

            $this->auditService->log(
                event: 'quote.sent',
                auditable: $quote,
                userId: $user?->id,
                tenantId: $quote->tenant_id
            );

            return $quote;
        });
    }

    public function acceptQuote(Quote $quote, ?User $user = null): Quote
    {
        if (! in_array($quote->status, [QuoteStatus::BROUILLON, QuoteStatus::ENVOYE], true)) {
            throw new InvalidArgumentException("Ce devis ne peut plus être accepté car son statut actuel est {$quote->status->label()}.");
        }

        return DB::transaction(function () use ($quote, $user) {
            $quote->status = QuoteStatus::ACCEPTE;
            $quote->accepted_at = now();
            $quote->save();

            $ticket = $quote->ticket;
            if ($ticket && $this->stateMachine->canTransition($ticket, RepairStatus::EN_REPARATION)) {
                $this->stateMachine->transition($ticket, RepairStatus::EN_REPARATION, $user, "Devis {$quote->reference} validé par le client");
            }

            $this->auditService->log(
                event: 'quote.accepted',
                auditable: $quote,
                userId: $user?->id,
                tenantId: $quote->tenant_id
            );

            return $quote;
        });
    }

    public function refuseQuote(Quote $quote, ?string $reason = null, ?User $user = null): Quote
    {
        if (! in_array($quote->status, [QuoteStatus::BROUILLON, QuoteStatus::ENVOYE], true)) {
            throw new InvalidArgumentException("Ce devis ne peut plus être refusé car son statut actuel est {$quote->status->label()}.");
        }

        return DB::transaction(function () use ($quote, $reason, $user) {
            $quote->status = QuoteStatus::REFUSE;
            $quote->refused_at = now();
            $quote->refusal_reason = $reason;
            $quote->save();

            $this->auditService->log(
                event: 'quote.refused',
                auditable: $quote,
                newValues: ['refusal_reason' => $reason],
                userId: $user?->id,
                tenantId: $quote->tenant_id
            );

            return $quote;
        });
    }
}
