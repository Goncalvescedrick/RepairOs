<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Services\AuditService;
use App\Services\PaymentService;
use App\Services\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected TenantManager $tenantManager,
        protected AuditService $auditService
    ) {}

    public function store(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('create', Payment::class);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', 'string', 'in:ESPECES,MOBILE_MONEY,VIREMENT,CARTE,AUTRE'],
            'transaction_reference' => ['nullable', 'string', 'max:100'],
            'paid_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $payment = $this->paymentService->recordPayment(
                invoice: $invoice,
                data: $validated,
                user: auth()->user()
            );

            return redirect()->route('invoices.show', $invoice)
                ->with('success', "Règlement de " . number_format($payment->amount, 0, ',', ' ') . " {$invoice->currency} enregistré avec succès (Reçu : {$payment->reference}).");
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function receipt(Payment $payment): View
    {
        $this->authorize('view', $payment);

        $payment->load(['invoice.customer', 'invoice.ticket.device', 'user', 'tenant']);

        return view('payments.receipt', compact('payment'));
    }

    public function cancel(Request $request, Payment $payment): RedirectResponse
    {
        $this->authorize('cancel', $payment);

        $validated = $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:255'],
        ]);

        try {
            $this->paymentService->cancelPayment(
                payment: $payment,
                reason: $validated['cancellation_reason'],
                user: auth()->user()
            );

            return redirect()->route('invoices.show', $payment->invoice)
                ->with('success', "Le règlement {$payment->reference} a été annulé.");
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
