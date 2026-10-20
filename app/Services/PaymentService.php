<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PaymentService
{
    public function __construct(
        protected ReferenceGeneratorService $referenceGenerator,
        protected AuditService $auditService
    ) {}

    public function recordPayment(Invoice $invoice, array $data, User $user): Payment
    {
        if ($invoice->status === InvoiceStatus::ANNULEE) {
            throw new InvalidArgumentException("Impossible d'encaisser un paiement sur une facture annulée.");
        }

        $amount = round((float) ($data['amount'] ?? 0), 2);

        if ($amount <= 0) {
            throw new InvalidArgumentException("Le montant du paiement doit être strictement supérieur à zéro.");
        }

        if ($amount > (float) $invoice->balance_due) {
            throw new InvalidArgumentException(
                "Le montant ({$amount} {$invoice->currency}) dépasse le solde restant dû ({$invoice->balance_due} {$invoice->currency})."
            );
        }

        $tenant = $invoice->tenant;
        $reference = $this->referenceGenerator->generatePaymentReference($tenant);

        return DB::transaction(function () use ($invoice, $tenant, $reference, $amount, $data, $user) {
            $paymentMethod = $data['payment_method'] instanceof PaymentMethod
                ? $data['payment_method']
                : PaymentMethod::tryFrom($data['payment_method'] ?? 'ESPECES') ?? PaymentMethod::ESPECES;

            $payment = Payment::create([
                'tenant_id' => $tenant->id,
                'invoice_id' => $invoice->id,
                'repair_ticket_id' => $invoice->repair_ticket_id,
                'user_id' => $user->id,
                'reference' => $reference,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'transaction_reference' => $data['transaction_reference'] ?? null,
                'paid_at' => $data['paid_at'] ?? now(),
                'notes' => $data['notes'] ?? null,
            ]);

            $newPaidAmount = round((float) $invoice->paid_amount + $amount, 2);
            $newBalanceDue = max(0.00, round((float) $invoice->total_amount - $newPaidAmount, 2));

            $invoice->paid_amount = $newPaidAmount;
            $invoice->balance_due = $newBalanceDue;

            if ($newBalanceDue <= 0.00) {
                $invoice->status = InvoiceStatus::PAYEE;
                $invoice->paid_at = now();
            } else {
                $invoice->status = InvoiceStatus::PARTIELLEMENT_PAYEE;
            }

            $invoice->save();

            $this->auditService->log(
                event: 'payment.recorded',
                auditable: $payment,
                newValues: [
                    'reference' => $payment->reference,
                    'amount' => $payment->amount,
                    'method' => $payment->payment_method->value,
                    'invoice_balance_due' => $invoice->balance_due,
                ],
                userId: $user->id,
                tenantId: $tenant->id
            );

            return $payment;
        });
    }

    public function cancelPayment(Payment $payment, string $reason, User $user): Payment
    {
        if ($payment->is_cancelled) {
            throw new InvalidArgumentException("Ce règlement est déjà annulé.");
        }

        if (empty(trim($reason))) {
            throw new InvalidArgumentException("Un motif explicite est requis pour annuler un paiement.");
        }

        return DB::transaction(function () use ($payment, $reason, $user) {
            $invoice = $payment->invoice;

            $payment->is_cancelled = true;
            $payment->cancellation_reason = $reason;
            $payment->cancelled_by_user_id = $user->id;
            $payment->save();

            // Revert on invoice
            $newPaidAmount = max(0.00, round((float) $invoice->paid_amount - (float) $payment->amount, 2));
            $newBalanceDue = max(0.00, round((float) $invoice->total_amount - $newPaidAmount, 2));

            $invoice->paid_amount = $newPaidAmount;
            $invoice->balance_due = $newBalanceDue;

            if ($newPaidAmount <= 0.00) {
                $invoice->status = InvoiceStatus::EMISE;
                $invoice->paid_at = null;
            } else {
                $invoice->status = InvoiceStatus::PARTIELLEMENT_PAYEE;
            }

            $invoice->save();

            $this->auditService->log(
                event: 'payment.cancelled',
                auditable: $payment,
                newValues: [
                    'reference' => $payment->reference,
                    'amount' => $payment->amount,
                    'reason' => $reason,
                    'invoice_balance_due' => $invoice->balance_due,
                ],
                userId: $user->id,
                tenantId: $payment->tenant_id
            );

            return $payment;
        });
    }
}
