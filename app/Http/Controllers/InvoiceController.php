<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Quote;
use App\Models\RepairTicket;
use App\Services\AuditService;
use App\Services\InvoiceService;
use App\Services\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService,
        protected TenantManager $tenantManager,
        protected AuditService $auditService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Invoice::class);

        $query = Invoice::query()->with(['customer', 'ticket.device', 'payments']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'LIKE', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'LIKE', "%{$search}%")
                         ->orWhere('phone', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $invoices = $query->orderByDesc('issued_at')->paginate(15)->withQueryString();

        return view('invoices.index', compact('invoices'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $this->authorize('create', Invoice::class);

        // If converting from an accepted Quote
        if ($quoteId = $request->input('quote_id')) {
            $quote = Quote::with(['items', 'customer', 'ticket'])->findOrFail($quoteId);
            return $this->convertFromQuote($quote);
        }

        $ticketId = $request->input('ticket_id');
        $ticket = null;
        if ($ticketId) {
            $ticket = RepairTicket::with(['customer', 'device'])->findOrFail($ticketId);
        }

        $tickets = RepairTicket::with(['customer', 'device'])
            ->orderByDesc('deposit_date')
            ->get();

        $tenant = $this->tenantManager->getTenant();

        return view('invoices.create', compact('ticket', 'tickets', 'tenant'));
    }

    public function convertFromQuote(Quote $quote): RedirectResponse
    {
        $this->authorize('create', Invoice::class);

        try {
            $invoice = $this->invoiceService->createFromQuote($quote, auth()->user());
            return redirect()->route('invoices.show', $invoice)
                ->with('success', "Facture {$invoice->reference} émise à partir du devis {$quote->reference}.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Invoice::class);

        $validated = $request->validate([
            'repair_ticket_id' => ['required', 'exists:repair_tickets,id'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.type' => ['required', 'string', 'in:SERVICE,PART,OTHER'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $ticket = RepairTicket::findOrFail($validated['repair_ticket_id']);

        $invoice = $this->invoiceService->createDirectInvoice(
            ticket: $ticket,
            data: [
                'tax_rate' => $validated['tax_rate'] ?? 0.00,
                'discount_amount' => $validated['discount_amount'] ?? 0.00,
                'due_date' => $validated['due_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ],
            items: $validated['items'],
            user: auth()->user()
        );

        return redirect()->route('invoices.show', $invoice)
            ->with('success', "Facture {$invoice->reference} émise avec succès.");
    }

    public function show(Invoice $invoice): View
    {
        $this->authorize('view', $invoice);

        $invoice->load([
            'ticket.device',
            'customer',
            'items',
            'payments.user',
            'tenant',
        ]);

        return view('invoices.show', compact('invoice'));
    }

    public function cancel(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('cancel', $invoice);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        try {
            $this->invoiceService->cancelInvoice($invoice, $validated['reason'], auth()->user());
            return redirect()->route('invoices.show', $invoice)
                ->with('success', "La facture {$invoice->reference} a été annulée.");
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function print(Invoice $invoice): View
    {
        $this->authorize('view', $invoice);

        $invoice->load(['ticket.device', 'customer', 'items', 'payments', 'tenant']);

        return view('invoices.print', compact('invoice'));
    }
}
