<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\RepairStatus;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\Device;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\RepairTicket;
use App\Models\Tenant;
use App\Models\TenantMembership;
use App\Models\User;
use App\Services\AttachmentStorageService;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use App\Services\TenantManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityAndIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;
    protected Tenant $tenantB;
    protected User $ownerA;
    protected User $technicianA;
    protected User $ownerB;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        // Atelier A (Ouaga)
        $this->tenantA = Tenant::create([
            'name' => 'Atelier Alpha Ouaga',
            'slug' => 'atelier-alpha',
            'currency' => 'XOF',
            'timezone' => 'Africa/Ouagadougou',
            'is_active' => true,
        ]);

        $this->ownerA = User::create([
            'name' => 'Patron Alpha',
            'email' => 'alpha.owner@repair.bf',
            'password' => bcrypt('secret123'),
            'is_active' => true,
        ]);

        TenantMembership::create([
            'tenant_id' => $this->tenantA->id,
            'user_id' => $this->ownerA->id,
            'role' => UserRole::OWNER->value,
            'is_active' => true,
        ]);

        $this->technicianA = User::create([
            'name' => 'Technicien Alpha',
            'email' => 'alpha.tech@repair.bf',
            'password' => bcrypt('secret123'),
            'is_active' => true,
        ]);

        TenantMembership::create([
            'tenant_id' => $this->tenantA->id,
            'user_id' => $this->technicianA->id,
            'role' => UserRole::TECHNICIAN->value,
            'is_active' => true,
        ]);

        // Atelier B (Bobo)
        $this->tenantB = Tenant::create([
            'name' => 'Atelier Bêta Bobo',
            'slug' => 'atelier-beta',
            'currency' => 'XOF',
            'timezone' => 'Africa/Ouagadougou',
            'is_active' => true,
        ]);

        $this->ownerB = User::create([
            'name' => 'Patron Bêta',
            'email' => 'beta.owner@repair.bf',
            'password' => bcrypt('secret123'),
            'is_active' => true,
        ]);

        TenantMembership::create([
            'tenant_id' => $this->tenantB->id,
            'user_id' => $this->ownerB->id,
            'role' => UserRole::OWNER->value,
            'is_active' => true,
        ]);
    }

    public function test_tenant_b_cannot_view_or_access_tenant_a_customer(): void
    {
        app(TenantManager::class)->setTenant($this->tenantA);

        $customerA = Customer::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Client Privé Alpha',
            'phone' => '+226 70 00 00 01',
        ]);

        // User B tries to view Customer A by ID
        $response = $this->actingAs($this->ownerB)
            ->withSession(['current_tenant_id' => $this->tenantB->id])
            ->get(route('customers.show', $customerA));

        // Global TenantScope automatically excludes it or returns 404
        $this->assertTrue(in_array($response->status(), [403, 404]));
    }

    public function test_tenant_b_cannot_view_or_access_tenant_a_ticket(): void
    {
        app(TenantManager::class)->setTenant($this->tenantA);

        $customerA = Customer::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Client Alpha',
            'phone' => '+226 70 00 00 02',
        ]);

        $deviceA = Device::create([
            'tenant_id' => $this->tenantA->id,
            'customer_id' => $customerA->id,
            'category' => 'LAPTOP',
            'brand' => 'HP',
            'model' => 'ProBook',
        ]);

        $ticketA = RepairTicket::create([
            'tenant_id' => $this->tenantA->id,
            'customer_id' => $customerA->id,
            'device_id' => $deviceA->id,
            'reference' => 'TICK-2026-0001',
            'status' => RepairStatus::RECU,
            'priority' => 'NORMALE',
            'reported_issue' => 'Panne carte mère',
            'deposit_date' => now(),
            'public_tracking_token' => 'random_tracking_token_alpha_1234567890abcdef',
        ]);

        $response = $this->actingAs($this->ownerB)
            ->withSession(['current_tenant_id' => $this->tenantB->id])
            ->get(route('tickets.show', $ticketA));

        $this->assertTrue(in_array($response->status(), [403, 404]));
    }

    public function test_technician_cannot_access_workshop_settings_or_team(): void
    {
        $settingsResponse = $this->actingAs($this->technicianA)
            ->withSession(['current_tenant_id' => $this->tenantA->id])
            ->get(route('settings.index'));

        // Technician is forbidden from settings
        $settingsResponse->assertStatus(403);

        $teamResponse = $this->actingAs($this->technicianA)
            ->withSession(['current_tenant_id' => $this->tenantA->id])
            ->get(route('team.index'));

        // Technician is forbidden from team management
        $teamResponse->assertStatus(403);
    }

    public function test_attachment_download_cross_tenant_is_forbidden(): void
    {
        app(TenantManager::class)->setTenant($this->tenantA);

        $customerA = Customer::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Client Alpha',
            'phone' => '+226 70 00 00 03',
        ]);

        $deviceA = Device::create([
            'tenant_id' => $this->tenantA->id,
            'customer_id' => $customerA->id,
            'category' => 'LAPTOP',
            'brand' => 'Lenovo',
            'model' => 'ThinkPad',
        ]);

        $fakeFile = UploadedFile::fake()->image('diagnostic.jpg', 600, 600);
        $attachmentService = app(AttachmentStorageService::class);
        $attachment = $attachmentService->storeAttachment($deviceA, $fakeFile, 'DEVICE_PHOTO', false, $this->ownerA);

        // User B tries to download Attachment A
        $response = $this->actingAs($this->ownerB)
            ->withSession(['current_tenant_id' => $this->tenantB->id])
            ->get(route('attachments.download', $attachment));

        $this->assertTrue(in_array($response->status(), [403, 404]));
    }

    public function test_public_tracking_with_invalid_or_tampered_token_returns_404(): void
    {
        $response = $this->get('/suivi/invalid-token-short');
        $response->assertStatus(404);

        $response2 = $this->get('/suivi/' . str_repeat('z', 64));
        $response2->assertStatus(404);
    }

    public function test_overpayment_is_strictly_rejected(): void
    {
        app(TenantManager::class)->setTenant($this->tenantA);

        $customerA = Customer::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Client Alpha',
            'phone' => '+226 70 00 00 04',
        ]);

        $deviceA = Device::create([
            'tenant_id' => $this->tenantA->id,
            'customer_id' => $customerA->id,
            'category' => 'LAPTOP',
            'brand' => 'Dell',
            'model' => 'Inspiron',
        ]);

        $ticketA = RepairTicket::create([
            'tenant_id' => $this->tenantA->id,
            'customer_id' => $customerA->id,
            'device_id' => $deviceA->id,
            'reference' => 'TICK-2026-9999',
            'status' => RepairStatus::EN_REPARATION,
            'priority' => 'NORMALE',
            'reported_issue' => 'Écran cassé',
            'deposit_date' => now(),
            'public_tracking_token' => 'token_overpay_test_1234567890abcdef1234567890abcdef',
        ]);

        $invoice = Invoice::create([
            'tenant_id' => $this->tenantA->id,
            'customer_id' => $customerA->id,
            'repair_ticket_id' => $ticketA->id,
            'reference' => 'FAC-2026-9999',
            'status' => InvoiceStatus::EMISE,
            'total_amount' => 15000.00,
            'balance_due' => 15000.00,
            'paid_amount' => 0.00,
            'currency' => 'XOF',
            'issued_at' => now(),
            'due_date' => now()->addDays(7),
        ]);

        $paymentService = app(PaymentService::class);

        // Attempting to pay 20,000 on a 15,000 balance must throw InvalidArgumentException
        $this->expectException(\InvalidArgumentException::class);
        $paymentService->recordPayment($invoice, [
            'amount' => 20000,
            'payment_method' => 'ESPECES',
            'paid_at' => now(),
        ], $this->ownerA);
    }

    public function test_deactivated_user_cannot_access_protected_routes(): void
    {
        $this->technicianA->update(['is_active' => false]);

        $response = $this->actingAs($this->technicianA)
            ->withSession(['current_tenant_id' => $this->tenantA->id])
            ->get(route('dashboard'));

        // Must redirect to login because account is deactivated
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
