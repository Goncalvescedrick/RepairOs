<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Quote;
use App\Models\RepairTicket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InvoiceService
{
    public function __construct(
        protected ReferenceGeneratorService $referenceGenerator,
        protected AuditService $auditService
    ) {}

    public function createFromQuote(Quote $quote, ?User $user = null): Invoice
    {
        $ticket = $quote->ticket;
        $tenant = $quote->tenant;

        return DB::transaction(function () use ($quote, $ticket, $tenant, $user) {
            $reference = $this->referenceGenerator->generateInvoiceReference($tenant);

            $invoice = Invoice::create([
                'tenant_id' => $tenant->id,
                'repair_ticket_id' => $ticket->id,
                'customer_id' => $quote->customer_id,
                'quote_id' => $quote->id,
                'reference' => $reference,
                'status' => InvoiceStatus::EMISE,
                'subtotal_amount' => $quote->subtotal_amount,
                'tax_rate' => $quote->tax_rate,
                'tax_amount' => $quote->tax_amount,
                'discount_amount' => $quote->discount_amount,
                'total_amount' => $quote->total_amount,
                'paid_amount' => 0.00,
                'balance_due' => $quote->total_amount,
                'currency' => $quote->currency ?: 'XOF',
                'due_date' => now()->addDays(30),
                'issued_at' => now(),
                'notes' => $quote->notes,
            ]);

            foreach ($quote->items as $item) {
                $invoice->items()->create([
                    'type' => $item->type,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                ]);
            }

            $this->auditService->log(
                event: 'invoice.issued_from_quote',
                auditable: $invoice,
                newValues: ['reference' => $invoice->reference, 'total_amount' => $invoice->total_amount],
                userId: $user?->id,
                tenantId: $tenant->id
            );

            return $invoice->load('items');
        });
    }

    public function createDirectInvoice(RepairTicket $ticket, array $data, array $items, ?User $user = null): Invoice
    {
        $tenant = $ticket->tenant;

        return DB::transaction(function () use ($ticket, $tenant, $data, $items, $user) {
            $reference = $this->referenceGenerator->generateInvoiceReference($tenant);
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
                    'description' => trim((string) ($item['description'] ?? 'Intervention technique')),
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'total_price' => $lineTotal,
                ];
            }

            $taxAmount = round($subtotal * ($taxRate / 100), 2);
            $totalAmount = max(0.00, round($subtotal - $discount + $taxAmount, 2));

            $invoice = Invoice::create([
                'tenant_id' => $tenant->id,
                'repair_ticket_id' => $ticket->id,
                'customer_id' => $ticket->customer_id,
                'reference' => $reference,
                'status' => InvoiceStatus::EMISE,
                'subtotal_amount' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discount,
                'total_amount' => $totalAmount,
                'paid_amount' => 0.00,
                'balance_due' => $totalAmount,
                'currency' => $tenant->currency ?: 'XOF',
                'due_date' => $data['due_date'] ?? now()->addDays(30),
                'issued_at' => now(),
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($parsedItems as $itemData) {
                $invoice->items()->create($itemData);
            }

            $this->auditService->log(
                event: 'invoice.issued_direct',
                auditable: $invoice,
                newValues: ['reference' => $invoice->reference, 'total_amount' => $invoice->total_amount],
                userId: $user?->id,
                tenantId: $tenant->id
            );

            return $invoice->load('items');
        });
    }

    public function cancelInvoice(Invoice $invoice, string $reason, ?User $user = null): Invoice
    {
        if ($invoice->status === InvoiceStatus::ANNULEE) {
            throw new InvalidArgumentException("Cette facture est déjà annulée.");
        }

        if ($invoice->paid_amount > 0) {
            throw new InvalidArgumentException("Impossible d'annuler une facture sur laquelle des paiements ont déjà été enregistrés. Annulez d'abord les règlements correspondants.");
        }

        return DB::transaction(function () use ($invoice, $reason, $user) {
            $invoice->status = InvoiceStatus::ANNULEE;
            $invoice->save();

            $this->auditService->log(
                event: 'invoice.cancelled',
                auditable: $invoice,
                newValues: ['reason' => $reason],
                userId: $user?->id,
                tenantId: $invoice->tenant_id
            );

            return $invoice;
        });
    }
}
