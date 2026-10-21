<?php

namespace App\Http\Controllers;

use App\Enums\RepairStatus;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\RepairIntervention;
use App\Models\RepairTicket;
use App\Models\User;
use App\Services\TenantManager;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        protected TenantManager $tenantManager
    ) {}

    public function index(Request $request): View
    {
        $tenant = $this->tenantManager->getTenant();
        $this->authorize('viewReports', $tenant);

        // Date range filters (default: current month)
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : Carbon::now()->startOfMonth();

        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : Carbon::now()->endOfDay();

        // 1. Financial metrics & Gross Margin
        $invoicesQuery = Invoice::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'ANNULEE');

        $totalInvoiced = (float) (clone $invoicesQuery)->sum('total_amount');

        $totalPartsCost = (float) InvoiceItem::whereHas('invoice', function ($q) use ($startDate, $endDate) {
            $q->whereBetween('created_at', [$startDate, $endDate])
                ->where('status', '!=', 'ANNULEE');
        })->selectRaw('COALESCE(SUM(quantity * purchase_cost_unit), 0) as total_cost')
            ->value('total_cost');

        $estimatedGrossMargin = max(0.0, round($totalInvoiced - $totalPartsCost, 2));

        $totalCollected = (float) Payment::whereBetween('paid_at', [$startDate, $endDate])
            ->where('is_cancelled', false)
            ->sum('amount');

        $totalBalanceDue = (float) Invoice::where('status', '!=', 'ANNULEE')
            ->where('balance_due', '>', 0)
            ->sum('balance_due');

        $invoicesCount = Invoice::whereBetween('created_at', [$startDate, $endDate])->count();
        $paymentsCount = Payment::whereBetween('paid_at', [$startDate, $endDate])->where('is_cancelled', false)->count();

        // 2. Repairs metrics
        $ticketsCreatedCount = RepairTicket::whereBetween('deposit_date', [$startDate, $endDate])->count();
        $ticketsClosedCount = RepairTicket::whereBetween('closed_at', [$startDate, $endDate])->count();

        // Average turnaround time (hours) for tickets closed in this period
        $closedTickets = RepairTicket::whereNotNull('closed_at')
            ->whereBetween('closed_at', [$startDate, $endDate])
            ->get();

        $averageTurnaroundDays = null;
        if ($closedTickets->isNotEmpty()) {
            $totalHours = $closedTickets->reduce(function ($carry, $ticket) {
                return $carry + $ticket->deposit_date->diffInHours($ticket->closed_at);
            }, 0);
            $avgHours = $totalHours / $closedTickets->count();
            $averageTurnaroundDays = round($avgHours / 24, 1);
        }

        // Breakdown by status (Single grouped query instead of N queries)
        $groupedStatuses = RepairTicket::query()
            ->select('status', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $statusCounts = [];
        foreach (RepairStatus::cases() as $case) {
            $statusCounts[$case->value] = [
                'label' => $case->label(),
                'count' => (int) ($groupedStatuses[$case->value] ?? 0),
            ];
        }

        // 3. Technician metrics
        $technicians = User::whereHas('memberships', function ($q) use ($tenant) {
            $q->where('tenant_id', $tenant->id)->where('is_active', true);
        })->get()->map(function ($tech) use ($startDate, $endDate) {
            $assignedCount = RepairTicket::where('assigned_to_user_id', $tech->id)
                ->whereBetween('deposit_date', [$startDate, $endDate])
                ->count();

            $completedCount = RepairTicket::where('assigned_to_user_id', $tech->id)
                ->where('status', RepairStatus::RESTITUE->value)
                ->whereBetween('closed_at', [$startDate, $endDate])
                ->count();

            $interventionsCount = RepairIntervention::where('user_id', $tech->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            return [
                'name' => $tech->name,
                'email' => $tech->email,
                'assigned' => $assignedCount,
                'completed' => $completedCount,
                'interventions' => $interventionsCount,
            ];
        });

        // 4. Audit Log trail
        $auditLogs = AuditLog::with('user')
            ->orderByDesc('id')
            ->limit(30)
            ->get();

        return view('reports.index', compact(
            'tenant',
            'startDate',
            'endDate',
            'totalInvoiced',
            'totalPartsCost',
            'estimatedGrossMargin',
            'totalCollected',
            'totalBalanceDue',
            'invoicesCount',
            'paymentsCount',
            'ticketsCreatedCount',
            'ticketsClosedCount',
            'averageTurnaroundDays',
            'statusCounts',
            'technicians',
            'auditLogs'
        ));
    }
}
