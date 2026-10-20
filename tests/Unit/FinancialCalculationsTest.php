<?php

namespace Tests\Unit;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Enums\QuoteStatus;
use App\Models\Customer;
use App\Models\Device;
use App\Models\RepairTicket;
use App\Models\Tenant;
use App\Models\User;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use App\Services\QuoteService;
use App\Services\TenantManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class FinancialCalculationsTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;
    protected Customer $customer;
    protected RepairTicket $ticket;
    protected QuoteService $quoteService;
    protected InvoiceService $invoiceService;
    protected PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->quoteService = app(QuoteService::class);
        $this->invoiceService = app(InvoiceService::class);
        $this->paymentService = app(PaymentService::class);

        $this->tenant = Tenant::create([
            'name' => 'Atelier Pro',
            'slug' => 'atelier-pro',
            'currency' => 'XOF',
            'tax_rate' => 18.00, // 18% TVA
        ]);
        app(TenantManager::class)->setTenant($this->tenant);

        $this->user = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@pro.bf',
            'password' => bcrypt('secret'),
        ]);

        $this->customer = Customer::create([
            'name' => 'Ablassé',
            'phone' => '70000000',
        ]);

        $device = Device::create([
            'customer_id' => $this->customer->id,
            'brand' => 'HP',
            'model' => 'Pavilion',
        ]);

        $this->ticket = RepairTicket::create([
            'reference' => 'TICK-2026-0001',
            'customer_id' => $this->customer->id,
            'device_id' => $device->id,
            'deposit_date' => now(),
            'reported_issue' => 'Clavier cassé',
            'public_tracking_token' => bin2hex(random_bytes(32)),
        ]);
    }

    public function test_quote_calculates_subtotal_taxes_discounts_accurately(): void
    {
        $items = [
            ['type' => 'PART', 'description' => 'Clavier rétroéclairé', 'quantity' => 1, 'unit_price' => 20000],
            ['type' => 'SERVICE', 'description' => 'Main d’œuvre remplacement', 'quantity' => 2, 'unit_price' => 5000],
        ];

        // Subtotal = 20 000 + 10 000 = 30 000
        // Discount = 2 000
        // Tax 18% on 30 000 = 5 400
        // Total = 30 000 - 2 000 + 5 400 = 33 400

        $quote = $this->quoteService->createQuote(
            ticket: $this->ticket,
            data: ['discount_amount' => 2000, 'tax_rate' => 18.00],
            items: $items,
            user: $this->user
        );

        $this->assertEquals(30000.00, (float) $quote->subtotal_amount);
        $this->assertEquals(2000.00, (float) $quote->discount_amount);
        $this->assertEquals(5400.00, (float) $quote->tax_amount);
        $this->assertEquals(33400.00, (float) $quote->total_amount);
        $this->assertEquals(QuoteStatus::BROUILLON, $quote->status);
    }

    public function test_invoice_creation_and_partial_and_full_payments(): void
    {
        $items = [
            ['type' => 'SERVICE', 'description' => 'Remplacement pâte thermique', 'quantity' => 1, 'unit_price' => 15000],
        ];

        $invoice = $this->invoiceService->createDirectInvoice(
            ticket: $this->ticket,
            data: ['tax_rate' => 0.00, 'discount_amount' => 0.00],
            items: $items,
            user: $this->user
        );

        $this->assertEquals(15000.00, (float) $invoice->total_amount);
        $this->assertEquals(15000.00, (float) $invoice->balance_due);
        $this->assertEquals(0.00, (float) $invoice->paid_amount);
        $this->assertEquals(InvoiceStatus::EMISE, $invoice->status);

        // 1. Partial payment: 5 000 XOF
        $payment1 = $this->paymentService->recordPayment(
            invoice: $invoice,
            data: [
                'amount' => 5000,
                'payment_method' => PaymentMethod::MOBILE_MONEY,
                'transaction_reference' => 'OM-TX-998877',
            ],
            user: $this->user
        );

        $invoice->refresh();
        $this->assertEquals(5000.00, (float) $invoice->paid_amount);
        $this->assertEquals(10000.00, (float) $invoice->balance_due);
        $this->assertEquals(InvoiceStatus::PARTIELLEMENT_PAYEE, $invoice->status);
        $this->assertEquals('REC-2026-0001', $payment1->reference);

        // 2. Reject overpayment (> 10 000)
        $this->expectException(InvalidArgumentException::class);
        $this->paymentService->recordPayment(
            invoice: $invoice,
            data: ['amount' => 12000, 'payment_method' => PaymentMethod::ESPECES],
            user: $this->user
        );
    }

    public function test_invoice_becomes_paid_when_balance_reaches_zero(): void
    {
        $invoice = $this->invoiceService->createDirectInvoice(
            ticket: $this->ticket,
            data: ['tax_rate' => 0.00],
            items: [['description' => 'Dépannage', 'quantity' => 1, 'unit_price' => 10000]],
            user: $this->user
        );

        $this->paymentService->recordPayment(
            invoice: $invoice,
            data: ['amount' => 10000, 'payment_method' => PaymentMethod::ESPECES],
            user: $this->user
        );

        $invoice->refresh();
        $this->assertEquals(10000.00, (float) $invoice->paid_amount);
        $this->assertEquals(0.00, (float) $invoice->balance_due);
        $this->assertEquals(InvoiceStatus::PAYEE, $invoice->status);
        $this->assertNotNull($invoice->paid_at);
        $this->assertTrue($invoice->isFullyPaid());
    }
}
