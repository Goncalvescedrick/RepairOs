<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\QuoteStatus;
use App\Enums\RepairStatus;
use App\Models\Customer;
use App\Models\Device;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Quote;
use App\Models\RepairTicket;
use App\Models\Tenant;
use App\Models\TenantMembership;
use App\Models\User;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use App\Services\QuoteService;
use App\Services\RepairStateMachine;
use App\Services\TenantManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EndToEndRepairWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_fifteen_step_repair_lifecycle(): void
    {
        // 1. Inscription d'un nouvel atelier et de son propriétaire
        $registerResponse = $this->post('/register', [
            'name' => 'Salif Ouedraogo',
            'email' => 'salif@ouaga-repare.bf',
            'phone' => '+226 70 12 34 56',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
            'workshop_name' => 'Ouaga PC Express',
            'city' => 'Ouagadougou',
            'currency' => 'XOF',
        ]);

        $registerResponse->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $user = User::where('email', 'salif@ouaga-repare.bf')->firstOrFail();
        $tenant = Tenant::where('slug', 'ouaga-pc-express')->firstOrFail();

        $membership = TenantMembership::where('user_id', $user->id)
            ->where('tenant_id', $tenant->id)
            ->firstOrFail();

        $this->assertEquals('owner', $membership->role);

        // Set tenant context for tenant-scoped tests
        app(TenantManager::class)->setTenant($tenant);

        // 2. Création d'un client
        $customerResponse = $this->actingAs($user)
            ->withSession(['current_tenant_id' => $tenant->id])
            ->post('/customers', [
                'name' => 'Kaboré Ibrahim',
                'phone' => '+226 78 90 12 34',
                'email' => 'ibrahim.kabore@email.bf',
                'address' => 'Ouaga 2000',
                'notes' => 'Client fidèle recommandé par l\'entreprise ABC',
            ]);

        $customer = Customer::where('phone', '+226 78 90 12 34')->firstOrFail();
        $this->assertEquals('Kaboré Ibrahim', $customer->name);
        $customerResponse->assertRedirect(route('customers.show', $customer));

        // 3. Enregistrement d'un appareil
        $deviceResponse = $this->actingAs($user)
            ->withSession(['current_tenant_id' => $tenant->id])
            ->post('/devices', [
                'customer_id' => $customer->id,
                'category' => 'LAPTOP',
                'brand' => 'Dell',
                'model' => 'Latitude 5420',
                'serial_number' => 'DL5420SN0987',
                'aesthetic_condition' => 'Rayures superficielles sur la coque supérieure',
                'reported_accessories' => 'Chargeur 65W d\'origine avec câble secteur',
            ]);

        $device = Device::where('serial_number', 'DL5420SN0987')->firstOrFail();
        $this->assertEquals('Dell', $device->brand);
        $deviceResponse->assertRedirect(route('devices.show', $device));

        // 4. Création d'un ticket de réparation (RECU)
        $ticketResponse = $this->actingAs($user)
            ->withSession(['current_tenant_id' => $tenant->id])
            ->post('/tickets', [
                'customer_id' => $customer->id,
                'device_id' => $device->id,
                'priority' => 'NORMALE',
                'reported_issue' => 'Écran noir au démarrage, ventilateur tourne à fond pendant 10s puis s\'éteint.',
                'intake_condition' => 'Aucun choc apparent. Témoin de charge clignote blanc/ambre.',
                'accessories_received' => 'Chargeur secteur',
                'internal_notes' => 'NOTE CONFIDENTIELLE: Soupçon de court-circuit sur la ligne 19V rail principal.',
                'client_notes' => 'Diagnostic pris en charge sous 24h ouvrées.',
            ]);

        $ticket = RepairTicket::where('customer_id', $customer->id)->firstOrFail();
        $this->assertEquals(RepairStatus::RECU, $ticket->status);
        $this->assertStringStartsWith('TICK-', $ticket->reference);
        $this->assertNotEmpty($ticket->public_tracking_token);

        // 5. Enregistrement d'une observation / diagnostic
        $interventionResponse = $this->actingAs($user)
            ->withSession(['current_tenant_id' => $tenant->id])
            ->post("/tickets/{$ticket->id}/interventions", [
            'intervention_type' => 'DIAGNOSTIC',
            'symptoms_observed' => 'Court-circuit sur condensateur céramique ligne d\'alimentation CPU.',
            'diagnostic' => 'Condensateur C342 en court-circuit franc à la masse. Mosfet d\'entrée OK.',
            'solution_envisaged' => 'Dessoudage et remplacement du condensateur cms + nettoyage aux ultrasons.',
            'duration_minutes' => 45,
            'internal_notes' => 'Composant prélevé sur carte donneuse Latitude.',
            'client_report' => 'Diagnostic établi : micro-composant d\'alimentation défectueux identifié et localisé sur la carte mère.',
        ]);

        $interventionResponse->assertRedirect(route('tickets.show', $ticket));
        $this->assertEquals(1, $ticket->interventions()->count());

        // 6. Transition du ticket vers DIAGNOSTIC
        $stateMachine = app(RepairStateMachine::class);
        $stateMachine->transition($ticket, RepairStatus::DIAGNOSTIC, $user, 'Début des investigations au banc de test');
        $ticket->refresh();
        $this->assertEquals(RepairStatus::DIAGNOSTIC, $ticket->status);

        // 7. Établissement d'un devis
        $quoteService = app(QuoteService::class);
        $quote = $quoteService->createQuote(
            ticket: $ticket,
            data: ['valid_until' => now()->addDays(15)],
            items: [
                [
                    'item_type' => 'LABOR',
                    'description' => 'Main d\'œuvre réparation micro-soudures carte mère',
                    'quantity' => 1,
                    'unit_price' => 25000,
                    'discount_amount' => 0,
                ],
                [
                    'item_type' => 'PART',
                    'description' => 'Condensateur CMS haute tension faible ESR',
                    'quantity' => 2,
                    'unit_price' => 2500,
                    'discount_amount' => 0,
                ],
            ],
            user: $user
        );

        $this->assertEquals(QuoteStatus::BROUILLON, $quote->status);
        $this->assertEquals('30000.00', $quote->total_amount);

        // 8. Envoi du devis
        $quoteService->sendQuote($quote, $user);
        $this->assertEquals(QuoteStatus::ENVOYE, $quote->status);

        // 9. Acceptation du devis par le client
        $quoteService->acceptQuote($quote, $user);
        $this->assertEquals(QuoteStatus::ACCEPTE, $quote->status);

        // 10. Avancement des travaux : EN_REPARATION (automatique à l'accord client) -> TEST -> PRET_A_RETIRER
        $ticket->refresh();
        $this->assertEquals(RepairStatus::EN_REPARATION, $ticket->status);

        $stateMachine->transition($ticket, RepairStatus::TEST, $user, 'Passage sur banc de charge et test mémoire');
        $ticket->refresh();
        $this->assertEquals(RepairStatus::TEST, $ticket->status);

        $stateMachine->transition($ticket, RepairStatus::PRET_A_RETIRER, $user, 'Tous les tests fonctionnels sont concluants');
        $ticket->refresh();
        $this->assertEquals(RepairStatus::PRET_A_RETIRER, $ticket->status);

        // 11. Génération de la facture depuis le devis
        $invoiceService = app(InvoiceService::class);
        $invoice = $invoiceService->createFromQuote($quote, $user);
        $this->assertEquals('30000.00', $invoice->total_amount);
        $this->assertEquals('30000.00', $invoice->balance_due);
        $this->assertEquals(InvoiceStatus::EMISE, $invoice->status);

        // 12. Enregistrement d'un acompte partiel (10 000 FCFA par Mobile Money)
        $paymentService = app(PaymentService::class);
        $p1 = $paymentService->recordPayment($invoice, [
            'amount' => 10000,
            'payment_method' => 'MOBILE_MONEY',
            'transaction_reference' => 'MM-BF-20261020-001',
            'paid_at' => now(),
            'notes' => 'Acompte versé par Orange Money',
        ], $user);

        $invoice->refresh();
        $this->assertEquals('10000.00', $invoice->paid_amount);
        $this->assertEquals('20000.00', $invoice->balance_due);
        $this->assertEquals(InvoiceStatus::PARTIELLEMENT_PAYEE, $invoice->status);
        $this->assertStringStartsWith('REC-', $p1->reference);

        // 13. Règlement du solde restant (20 000 FCFA en espèces)
        $p2 = $paymentService->recordPayment($invoice, [
            'amount' => 20000,
            'payment_method' => 'ESPECES',
            'paid_at' => now(),
            'notes' => 'Solde réglé en espèces lors du retrait',
        ], $user);

        $invoice->refresh();
        $this->assertEquals('30000.00', $invoice->paid_amount);
        $this->assertEquals('0.00', $invoice->balance_due);
        $this->assertEquals(InvoiceStatus::PAYEE, $invoice->status);

        // 14. Consultation du suivi public sécurisé (/suivi/{token})
        $publicResponse = $this->get("/suivi/{$ticket->public_tracking_token}");
        $publicResponse->assertStatus(200);
        $publicResponse->assertSee($ticket->reference);
        $publicResponse->assertSee('Dell Latitude 5420');
        $publicResponse->assertSee('Prêt à être retiré');
        // VÉRIFICATION STRICTE DE CONFIDENTIALITÉ: aucune note interne ou secret de réparation ne fuit
        $publicResponse->assertDontSee('NOTE CONFIDENTIELLE');
        $publicResponse->assertDontSee('Composant prélevé sur carte donneuse');

        // 15. Restitution finale au client
        $stateMachine->transition($ticket, RepairStatus::RESTITUE, $user, 'Appareil remis au client en main propre');
        $ticket->refresh();
        $this->assertEquals(RepairStatus::RESTITUE, $ticket->status);
        $this->assertNotNull($ticket->closed_at);
    }
}
