<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Enums\QuoteStatus;
use App\Enums\RepairStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Quote;
use App\Models\RepairStatusHistory;
use App\Models\RepairTicket;
use App\Models\SparePart;
use App\Services\TenantManager;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager
    ) {}

    public function index(Request $request): View
    {
        $tenant = $this->tenantManager->getTenant();
        $currency = $tenant ? $tenant->currency : 'XOF';

        // 1. Core KPIs
        $ticketsOpenCount = RepairTicket::whereNotIn('status', [RepairStatus::RESTITUE, RepairStatus::ANNULE])->count();
        $inDiagnosticCount = RepairTicket::where('status', RepairStatus::DIAGNOSTIC)->count();
        $inRepairCount = RepairTicket::whereIn('status', [RepairStatus::EN_REPARATION, RepairStatus::TEST])->count();
        $readyForPickupCount = RepairTicket::where('status', RepairStatus::PRET_A_RETIRER)->count();

        $quotesPendingCount = Quote::whereIn('status', [QuoteStatus::BROUILLON, QuoteStatus::ENVOYE])->count();

        // Late tickets: open tickets whose estimated_completion_date is past
        $overdueTicketsCount = RepairTicket::whereNotIn('status', [RepairStatus::RESTITUE, RepairStatus::ANNULE])
            ->whereNotNull('estimated_completion_date')
            ->where('estimated_completion_date', '<', now())
            ->count();

        // 2. Financial KPIs
        // Payments received this month
        $paymentsThisMonth = Payment::where('is_cancelled', false)
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        // Total outstanding balance across all non-cancelled invoices
        $totalOutstandingBalance = Invoice::where('status', '!=', InvoiceStatus::ANNULEE)
            ->sum('balance_due');

        // 3. Recent Tickets & Activity
        $recentTickets = RepairTicket::with(['customer', 'device', 'assignedTo'])
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        $recentHistories = RepairStatusHistory::with(['ticket.device', 'user'])
            ->orderByDesc('created_at')
            ->take(6)
            ->get();

        $lowStockPartsCount = SparePart::where('is_active', true)
            ->whereColumn('stock_quantity', '<=', 'min_stock_alert')
            ->count();

        return view('dashboard', compact(
            'tenant',
            'currency',
            'ticketsOpenCount',
            'inDiagnosticCount',
            'inRepairCount',
            'readyForPickupCount',
            'quotesPendingCount',
            'overdueTicketsCount',
            'paymentsThisMonth',
            'totalOutstandingBalance',
            'recentTickets',
            'recentHistories',
            'lowStockPartsCount'
        ));
    }
}
