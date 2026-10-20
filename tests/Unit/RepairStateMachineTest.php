<?php

namespace Tests\Unit;

use App\Enums\RepairStatus;
use App\Models\Customer;
use App\Models\Device;
use App\Models\Invoice;
use App\Models\RepairTicket;
use App\Models\Tenant;
use App\Models\User;
use App\Services\RepairStateMachine;
use App\Services\TenantManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class RepairStateMachineTest extends TestCase
{
    use RefreshDatabase;

    protected RepairStateMachine $stateMachine;
    protected Tenant $tenant;
    protected User $user;
    protected RepairTicket $ticket;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stateMachine = app(RepairStateMachine::class);

        $this->tenant = Tenant::create([
            'name' => 'Faso Tech',
            'slug' => 'faso-tech',
        ]);
        app(TenantManager::class)->setTenant($this->tenant);

        $this->user = User::create([
            'name' => 'Technicien Ali',
            'email' => 'ali@faso.bf',
            'password' => bcrypt('secret'),
        ]);

        $customer = Customer::create([
            'name' => 'Ousmane',
            'phone' => '70123456',
        ]);

        $device = Device::create([
            'customer_id' => $customer->id,
            'brand' => 'Lenovo',
            'model' => 'ThinkPad T480',
        ]);

        $this->ticket = RepairTicket::create([
            'reference' => 'TICK-2026-0001',
            'customer_id' => $customer->id,
            'device_id' => $device->id,
            'status' => RepairStatus::RECU,
            'deposit_date' => now(),
            'reported_issue' => 'Ventilateur bruyant',
            'public_tracking_token' => bin2hex(random_bytes(32)),
        ]);
    }

    public function test_can_transition_from_recu_to_diagnostic(): void
    {
        $this->assertTrue($this->stateMachine->canTransition($this->ticket, RepairStatus::DIAGNOSTIC));

        $updated = $this->stateMachine->transition($this->ticket, RepairStatus::DIAGNOSTIC, $this->user);

        $this->assertEquals(RepairStatus::DIAGNOSTIC, $updated->status);
        $this->assertCount(1, $this->ticket->statusHistories);
        $this->assertEquals(RepairStatus::RECU, $this->ticket->statusHistories->first()->from_status);
        $this->assertEquals(RepairStatus::DIAGNOSTIC, $this->ticket->statusHistories->first()->to_status);
    }

    public function test_cannot_make_arbitrary_illegal_transition(): void
    {
        // RECU cannot transition directly to PRET_A_RETIRER or RESTITUE
        $this->assertFalse($this->stateMachine->canTransition($this->ticket, RepairStatus::PRET_A_RETIRER));

        $this->expectException(InvalidArgumentException::class);
        $this->stateMachine->transition($this->ticket, RepairStatus::PRET_A_RETIRER, $this->user);
    }

    public function test_cancellation_requires_explicit_reason(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->stateMachine->transition($this->ticket, RepairStatus::ANNULE, $this->user, '');
    }

    public function test_restitution_blocked_if_unpaid_invoice_exists(): void
    {
        $this->ticket->status = RepairStatus::PRET_A_RETIRER;
        $this->ticket->save();

        Invoice::create([
            'repair_ticket_id' => $this->ticket->id,
            'customer_id' => $this->ticket->customer_id,
            'reference' => 'FAC-2026-0001',
            'status' => 'EMISE',
            'total_amount' => 25000,
            'paid_amount' => 0,
            'balance_due' => 25000,
            'issued_at' => now(),
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->stateMachine->transition($this->ticket, RepairStatus::RESTITUE, $this->user);
    }
}
