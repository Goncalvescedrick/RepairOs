<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\RepairTicket;
use App\Services\AuditService;
use App\Services\NotificationMessageService;
use App\Services\QuoteService;
use App\Services\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuoteController extends Controller
{
    public function __construct(
        protected QuoteService $quoteService,
        protected TenantManager $tenantManager,
        protected AuditService $auditService,
        protected NotificationMessageService $notificationService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Quote::class);

        $query = Quote::query()->with(['customer', 'ticket.device']);

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

        $quotes = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        return view('quotes.index', compact('quotes'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Quote::class);

        $ticketId = $request->input('ticket_id');
        $ticket = null;
        if ($ticketId) {
            $ticket = RepairTicket::with(['customer', 'device'])->findOrFail($ticketId);
        }

        $tickets = RepairTicket::with(['customer', 'device'])
            ->whereNotIn('status', ['RESTITUE', 'ANNULE'])
            ->orderByDesc('deposit_date')
            ->get();

        $tenant = $this->tenantManager->getTenant();

        return view('quotes.create', compact('ticket', 'tickets', 'tenant'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Quote::class);

        $validated = $request->validate([
            'repair_ticket_id' => ['required', 'exists:repair_tickets,id'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.type' => ['required', 'string', 'in:SERVICE,PART,OTHER'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $ticket = RepairTicket::findOrFail($validated['repair_ticket_id']);

        $quote = $this->quoteService->createQuote(
            ticket: $ticket,
            data: [
                'tax_rate' => $validated['tax_rate'] ?? 0.00,
                'discount_amount' => $validated['discount_amount'] ?? 0.00,
                'valid_until' => $validated['valid_until'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ],
            items: $validated['items'],
            user: auth()->user()
        );

        return redirect()->route('quotes.show', $quote)
            ->with('success', "Devis {$quote->reference} créé avec succès au format brouillon.");
    }

    public function show(Quote $quote): View
    {
        $this->authorize('view', $quote);

        $quote->load(['ticket.device', 'customer', 'items', 'invoices', 'tenant']);

        $quoteMessage = $this->notificationService->getQuoteMessage($quote);
        $whatsAppUrl = $this->notificationService->generateWhatsAppUrl($quote->customer->phone, $quoteMessage);

        return view('quotes.show', compact('quote', 'quoteMessage', 'whatsAppUrl'));
    }

    public function send(Quote $quote): RedirectResponse
    {
        $this->authorize('update', $quote);

        try {
            $this->quoteService->markAsSent($quote, auth()->user());
            return redirect()->route('quotes.show', $quote)
                ->with('success', "Le devis {$quote->reference} a été marqué comme envoyé.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function accept(Quote $quote): RedirectResponse
    {
        $this->authorize('acceptOrRefuse', $quote);

        try {
            $this->quoteService->acceptQuote($quote, auth()->user());
            return redirect()->route('quotes.show', $quote)
                ->with('success', "Le devis {$quote->reference} a été accepté. Le ticket est passé en cours de réparation.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function refuse(Request $request, Quote $quote): RedirectResponse
    {
        $this->authorize('acceptOrRefuse', $quote);

        $validated = $request->validate([
            'refusal_reason' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->quoteService->refuseQuote($quote, $validated['refusal_reason'] ?? null, auth()->user());
            return redirect()->route('quotes.show', $quote)
                ->with('success', "Le devis a été marqué comme refusé.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function print(Quote $quote): View
    {
        $this->authorize('view', $quote);

        $quote->load(['ticket.device', 'customer', 'items', 'tenant']);

        return view('quotes.print', compact('quote'));
    }
}
