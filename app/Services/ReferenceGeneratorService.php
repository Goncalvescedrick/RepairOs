<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Quote;
use App\Models\RepairTicket;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class ReferenceGeneratorService
{
    /**
     * Generate an atomic, concurrency-safe reference for a Repair Ticket.
     */
    public function generateTicketReference(Tenant $tenant): string
    {
        $prefix = $tenant->ticket_prefix ?: 'TICK';
        $year = date('Y');
        $pattern = "{$prefix}-{$year}-";

        return DB::transaction(function () use ($tenant, $pattern) {
            $lastTicket = RepairTicket::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->where('reference', 'LIKE', "{$pattern}%")
                ->orderByRaw('CAST(SUBSTRING(reference, -4) AS UNSIGNED) DESC')
                ->lockForUpdate()
                ->first();

            $nextNumber = 1;
            if ($lastTicket) {
                $lastNumber = (int) substr($lastTicket->reference, strlen($pattern));
                $nextNumber = $lastNumber + 1;
            }

            return sprintf('%s%04d', $pattern, $nextNumber);
        });
    }

    /**
     * Generate an atomic, concurrency-safe reference for a Quote.
     */
    public function generateQuoteReference(Tenant $tenant): string
    {
        $prefix = $tenant->quote_prefix ?: 'DEV';
        $year = date('Y');
        $pattern = "{$prefix}-{$year}-";

        return DB::transaction(function () use ($tenant, $pattern) {
            $lastQuote = Quote::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->where('reference', 'LIKE', "{$pattern}%")
                ->orderByRaw('CAST(SUBSTRING(reference, -4) AS UNSIGNED) DESC')
                ->lockForUpdate()
                ->first();

            $nextNumber = 1;
            if ($lastQuote) {
                $lastNumber = (int) substr($lastQuote->reference, strlen($pattern));
                $nextNumber = $lastNumber + 1;
            }

            return sprintf('%s%04d', $pattern, $nextNumber);
        });
    }

    /**
     * Generate an atomic, concurrency-safe reference for an Invoice.
     */
    public function generateInvoiceReference(Tenant $tenant): string
    {
        $prefix = $tenant->invoice_prefix ?: 'FAC';
        $year = date('Y');
        $pattern = "{$prefix}-{$year}-";

        return DB::transaction(function () use ($tenant, $pattern) {
            $lastInvoice = Invoice::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->where('reference', 'LIKE', "{$pattern}%")
                ->orderByRaw('CAST(SUBSTRING(reference, -4) AS UNSIGNED) DESC')
                ->lockForUpdate()
                ->first();

            $nextNumber = 1;
            if ($lastInvoice) {
                $lastNumber = (int) substr($lastInvoice->reference, strlen($pattern));
                $nextNumber = $lastNumber + 1;
            }

            return sprintf('%s%04d', $pattern, $nextNumber);
        });
    }

    /**
     * Generate an atomic, concurrency-safe reference for a Payment receipt.
     */
    public function generatePaymentReference(Tenant $tenant): string
    {
        $prefix = 'REC';
        $year = date('Y');
        $pattern = "{$prefix}-{$year}-";

        return DB::transaction(function () use ($tenant, $pattern) {
            $lastPayment = Payment::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->where('reference', 'LIKE', "{$pattern}%")
                ->orderByRaw('CAST(SUBSTRING(reference, -4) AS UNSIGNED) DESC')
                ->lockForUpdate()
                ->first();

            $nextNumber = 1;
            if ($lastPayment) {
                $lastNumber = (int) substr($lastPayment->reference, strlen($pattern));
                $nextNumber = $lastNumber + 1;
            }

            return sprintf('%s%04d', $pattern, $nextNumber);
        });
    }
}
