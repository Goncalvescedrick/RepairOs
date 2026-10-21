<?php

namespace Tests\Feature;

use App\Enums\QuoteStatus;
use App\Enums\RepairStatus;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\Device;
use App\Models\Quote;
use App\Models\RepairTicket;
use App\Models\SparePart;
use App\Models\Tenant;
use App\Models\TenantMembership;
use App\Models\User;
use App\Services\InvoiceService;
use App\Services\QuoteService;
use App\Services\RepairStateMachine;
use App\Services\TenantManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RepairOsInnovationsTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;

    protected Tenant $tenantB;

    protected User $managerA;

    protected Customer $customerA;

    protected Device $deviceA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantA = Tenant::create([
            'name' => 'Atelier Alpha',
            'slug' => 'atelier-alpha',
            'currency' => 'XOF',
            'tax_rate' => 18.00,
            'is_active' => true,
        ]);

        $this->tenantB = Tenant::create([
            'name' => 'Atelier Beta',
            'slug' => 'atelier-beta',
            'currency' => 'XOF',
            'tax_rate' => 18.00,
            'is_active' => true,
        ]);

        $this->managerA = User::create([
            'name' => 'Manager Alpha',
            'email' => 'manager@alpha.bf',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        TenantMembership::create([
            'tenant_id' => $this->tenantA->id,
            'user_id' => $this->managerA->id,
            'role' => UserRole::ADMIN->value,
            'is_active' => true,
        ]);

        app(TenantManager::class)->setTenant($this->tenantA);

        $this->customerA = Customer::create([
            'name' => 'Client Alpha',
            'phone' => '70112233',
        ]);

        $this->deviceA = Device::create([
            'customer_id' => $this->customerA->id,
            'device_category' => 'LAPTOP',
            'brand' => 'Lenovo',
            'model' => 'ThinkPad X1',
        ]);
    }

    public function test_cross_tenant_exists_validation_rejects_foreign_customer_on_ticket_creation(): void
    {
        app(TenantManager::class)->setTenant($this->tenantB);
        $customerB = Customer::create([
            'name' => 'Client Beta',
            'phone' => '76998877',
        ]);
        $deviceB = Device::create([
            'customer_id' => $customerB->id,
            'brand' => 'Dell',
            'model' => 'XPS',
        ]);

        app(TenantManager::class)->setTenant($this->tenantA);

        $response = $this->actingAs($this->managerA)
            ->withSession(['current_tenant_id' => $this->tenantA->id])
            ->post(route('tickets.store'), [
                'customer_id' => $customerB->id,
                'device_id' => $deviceB->id,
                'reported_issue' => 'Écran noir au démarrage',
                'priority' => 'NORMALE',
            ]);

        $response->assertSessionHasErrors(['customer_id', 'device_id']);
    }

    public function test_access_code_encryption_auto_purge_and_warranty_activation_on_restitue(): void
    {
        $ticket = RepairTicket::create([
            'reference' => 'TICK-2026-0001',
            'customer_id' => $this->customerA->id,
            'device_id' => $this->deviceA->id,
            'status' => RepairStatus::PRET_A_RETIRER,
            'deposit_date' => now(),
            'reported_issue' => 'Remplacement batterie',
            'warranty_days' => 90,
            'public_tracking_token' => bin2hex(random_bytes(32)),
        ]);

        $ticket->setAccessCode('2580#');
        $ticket->save();

        $this->assertNotNull($ticket->device_access_code_encrypted);
        $this->assertNotEquals('2580#', $ticket->device_access_code_encrypted);
        $this->assertEquals('2580#', $ticket->decrypted_access_code);
        $this->assertNull($ticket->warranty_expires_at);

        app(RepairStateMachine::class)->transition(
            ticket: $ticket,
            targetStatus: RepairStatus::RESTITUE,
            user: $this->managerA,
            reason: 'Remis au client en main propre'
        );

        $ticket->refresh();
        $this->assertEquals(RepairStatus::RESTITUE, $ticket->status);
        $this->assertNull($ticket->device_access_code_encrypted);
        $this->assertNull($ticket->decrypted_access_code);
        $this->assertNotNull($ticket->warranty_expires_at);
        $this->assertTrue($ticket->isUnderWarranty());
    }

    public function test_public_quote_approval_transitions_ticket_to_en_reparation(): void
    {
        $ticket = RepairTicket::create([
            'reference' => 'TICK-2026-0002',
            'customer_id' => $this->customerA->id,
            'device_id' => $this->deviceA->id,
            'status' => RepairStatus::DEVIS_EN_ATTENTE,
            'deposit_date' => now(),
            'reported_issue' => 'Charnière cassée',
            'public_tracking_token' => bin2hex(random_bytes(32)),
        ]);

        /** @var Quote $quote */
        $quote = app(QuoteService::class)->createQuote(
            ticket: $ticket,
            data: ['tax_rate' => 0, 'discount_amount' => 0],
            items: [
                ['type' => 'PART', 'description' => 'Kit charnières', 'quantity' => 1, 'unit_price' => 15000],
            ],
            user: $this->managerA
        );

        app(QuoteService::class)->sendQuote($quote, $this->managerA);

        $response = $this->post(route('public.tracking.quote.accept', [
            'token' => $ticket->public_tracking_token,
            'quote' => $quote->id,
        ]), [
            'approved_by_name' => 'Moussa Ouédraogo',
            'approval_note' => 'OK pour réparation rapide',
        ]);

        $response->assertRedirect(route('public.tracking.show', $ticket->public_tracking_token));

        $quote->refresh();
        $ticket->refresh();

        $this->assertEquals(QuoteStatus::ACCEPTE, $quote->status);
        $this->assertEquals('Moussa Ouédraogo', $quote->approved_by_name);
        $this->assertNotNull($quote->approved_ip);
        $this->assertEquals(RepairStatus::EN_REPARATION, $ticket->status);
    }

    public function test_spare_part_stock_decrements_on_invoice_and_restores_on_cancellation(): void
    {
        $part = SparePart::create([
            'name' => 'SSD NVMe 512 Go',
            'sku' => 'SSD-512-NVME',
            'category' => 'Stockage',
            'purchase_price' => 22000,
            'selling_price' => 35000,
            'stock_quantity' => 5,
            'min_stock_alert' => 2,
        ]);

        $ticket = RepairTicket::create([
            'reference' => 'TICK-2026-0003',
            'customer_id' => $this->customerA->id,
            'device_id' => $this->deviceA->id,
            'status' => RepairStatus::PRET_A_RETIRER,
            'deposit_date' => now(),
            'reported_issue' => 'Upgrade SSD',
            'public_tracking_token' => bin2hex(random_bytes(32)),
        ]);

        $invoiceService = app(InvoiceService::class);

        $invoice = $invoiceService->createDirectInvoice(
            ticket: $ticket,
            data: ['tax_rate' => 0, 'discount_amount' => 0],
            items: [
                [
                    'type' => 'PART',
                    'spare_part_id' => $part->id,
                    'description' => $part->name,
                    'quantity' => 2,
                    'unit_price' => 35000,
                ],
            ],
            user: $this->managerA
        );

        $part->refresh();
        $this->assertEquals(3, $part->stock_quantity);
        $this->assertEquals(22000.00, (float) $invoice->items->first()->purchase_cost_unit);

        $invoiceService->cancelInvoice($invoice, $this->managerA);

        $part->refresh();
        $this->assertEquals(5, $part->stock_quantity);
    }

    public function test_attachment_upload_supports_full_morph_class_and_intake_photo_category(): void
    {
        Storage::fake('local');

        $ticket = RepairTicket::create([
            'reference' => 'TICK-2026-0004',
            'customer_id' => $this->customerA->id,
            'device_id' => $this->deviceA->id,
            'status' => RepairStatus::RECU,
            'deposit_date' => now(),
            'reported_issue' => 'Rayures coque',
            'public_tracking_token' => bin2hex(random_bytes(32)),
        ]);

        $file = UploadedFile::fake()->image('etat-initial.jpg', 600, 400);

        $response = $this->actingAs($this->managerA)
            ->withSession(['current_tenant_id' => $this->tenantA->id])
            ->post(route('attachments.store'), [
                'attachable_type' => RepairTicket::class,
                'attachable_id' => $ticket->id,
                'file_category' => 'INTAKE_PHOTO',
                'file' => $file,
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals(1, $ticket->attachments()->count());
        $this->assertEquals('INTAKE_PHOTO', $ticket->attachments()->first()->file_category);
    }
}
