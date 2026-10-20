<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\Device;
use App\Models\RepairTicket;
use App\Models\Tenant;
use App\Models\TenantMembership;
use App\Models\User;
use App\Services\TenantManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;
    protected Tenant $tenantB;
    protected User $userA;
    protected User $userB;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Tenant A and Owner A
        $this->tenantA = Tenant::create([
            'name' => 'Atelier Alpha Ouaga',
            'slug' => 'atelier-alpha',
            'currency' => 'XOF',
            'timezone' => 'Africa/Ouagadougou',
            'is_active' => true,
        ]);

        $this->userA = User::create([
            'name' => 'Patron Alpha',
            'email' => 'alpha@repair.bf',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);

        TenantMembership::create([
            'tenant_id' => $this->tenantA->id,
            'user_id' => $this->userA->id,
            'role' => UserRole::OWNER->value,
            'is_active' => true,
        ]);

        // Create Tenant B and Owner B
        $this->tenantB = Tenant::create([
            'name' => 'Atelier Bêta Bobo',
            'slug' => 'atelier-beta',
            'currency' => 'XOF',
            'timezone' => 'Africa/Ouagadougou',
            'is_active' => true,
        ]);

        $this->userB = User::create([
            'name' => 'Patron Bêta',
            'email' => 'beta@repair.bf',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);

        TenantMembership::create([
            'tenant_id' => $this->tenantB->id,
            'user_id' => $this->userB->id,
            'role' => UserRole::OWNER->value,
            'is_active' => true,
        ]);
    }

    public function test_models_created_under_tenant_a_are_scoped_to_tenant_a(): void
    {
        // Set Tenant A
        app(TenantManager::class)->setTenant($this->tenantA);

        $customerA = Customer::create([
            'name' => 'Moussa Ouédraogo',
            'phone' => '+226 70 00 11 22',
        ]);

        $this->assertEquals($this->tenantA->id, $customerA->tenant_id);

        // Verify Customer is visible under Tenant A scope
        $this->assertCount(1, Customer::all());

        // Now switch to Tenant B
        app(TenantManager::class)->setTenant($this->tenantB);

        // Under Tenant B, Customer A must NOT be visible
        $this->assertCount(0, Customer::all());
        $this->assertNull(Customer::find($customerA->id));
    }

    public function test_tenant_b_cannot_find_or_manipulate_tenant_a_tickets(): void
    {
        app(TenantManager::class)->setTenant($this->tenantA);

        $customerA = Customer::create([
            'name' => 'Client A',
            'phone' => '70112233',
        ]);

        $deviceA = Device::create([
            'customer_id' => $customerA->id,
            'brand' => 'Dell',
            'model' => 'Latitude 5420',
            'category' => 'laptop',
        ]);

        $ticketA = RepairTicket::create([
            'reference' => 'TICK-2026-0001',
            'customer_id' => $customerA->id,
            'device_id' => $deviceA->id,
            'deposit_date' => now(),
            'reported_issue' => 'Écran noir après chute',
            'public_tracking_token' => bin2hex(random_bytes(32)),
        ]);

        $this->assertEquals($this->tenantA->id, $ticketA->tenant_id);

        // Switch to Tenant B
        app(TenantManager::class)->setTenant($this->tenantB);

        // Querying tickets returns empty
        $this->assertCount(0, RepairTicket::all());
        $this->assertNull(RepairTicket::find($ticketA->id));
    }

    public function test_global_scope_does_not_leak_when_querying_relationships(): void
    {
        app(TenantManager::class)->setTenant($this->tenantA);

        $customerA = Customer::create([
            'name' => 'Client Relation',
            'phone' => '70998877',
        ]);

        Device::create([
            'customer_id' => $customerA->id,
            'brand' => 'HP',
            'model' => 'ProBook 450',
            'category' => 'laptop',
        ]);

        // Under Tenant A, customer has 1 device
        $this->assertCount(1, $customerA->devices);

        // Switch to Tenant B
        app(TenantManager::class)->setTenant($this->tenantB);

        // Customer query returns null, and devices count is 0
        $this->assertCount(0, Device::all());
    }
}
