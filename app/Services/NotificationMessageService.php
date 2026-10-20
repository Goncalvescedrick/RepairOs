<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Quote;
use App\Models\RepairTicket;

class NotificationMessageService
{
    /**
     * Clean phone number for WhatsApp link (removes spaces, +, dashes).
     */
    public function cleanPhoneNumber(string $phone, string $defaultCountryCode = '226'): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        // If local 8 digits (standard Burkina Faso), prepend 226
        if (strlen($cleaned) === 8) {
            $cleaned = $defaultCountryCode . $cleaned;
        }

        return $cleaned;
    }

    /**
     * Message for ticket intake (device received).
     */
    public function getIntakeMessage(RepairTicket $ticket): string
    {
        $tenant = $ticket->tenant;
        $device = $ticket->device;
        $trackingUrl = url("/suivi/{$ticket->public_tracking_token}");

        return "Bonjour {$ticket->customer->name},\n\n"
            . "Votre équipement {$device->brand} {$device->model} a bien été pris en charge chez {$tenant->name}.\n"
            . "Référence du ticket : {$ticket->reference}\n"
            . "Problème déclaré : {$ticket->reported_issue}\n\n"
            . "Vous pouvez suivre l'avancement de votre réparation en temps réel via ce lien sécurisé :\n"
            . "{$trackingUrl}\n\n"
            . "Merci pour votre confiance.\n"
            . "— {$tenant->name} (" . ($tenant->phone ?: '') . ")";
    }

    /**
     * Message for Quote submission.
     */
    public function getQuoteMessage(Quote $quote): string
    {
        $ticket = $quote->ticket;
        $tenant = $quote->tenant;
        $trackingUrl = url("/suivi/{$ticket->public_tracking_token}");

        return "Bonjour {$quote->customer->name},\n\n"
            . "Le devis pour la réparation de votre {$ticket->device->brand} {$ticket->device->model} (Ticket {$ticket->reference}) est disponible.\n"
            . "Référence devis : {$quote->reference}\n"
            . "Montant total : " . number_format($quote->total_amount, 0, ',', ' ') . " {$quote->currency}\n\n"
            . "Consultez le détail et le suivi ici :\n"
            . "{$trackingUrl}\n\n"
            . "Merci de nous confirmer votre accord pour lancer les travaux.\n"
            . "— {$tenant->name}";
    }

    /**
     * Message when device is ready for pickup.
     */
    public function getReadyForPickupMessage(RepairTicket $ticket, ?Invoice $invoice = null): string
    {
        $tenant = $ticket->tenant;
        $device = $ticket->device;
        $balanceText = "";

        if ($invoice && (float) $invoice->balance_due > 0) {
            $balanceText = "Solde restant à régler : " . number_format($invoice->balance_due, 0, ',', ' ') . " {$invoice->currency}.\n";
        }

        return "Bonjour {$ticket->customer->name},\n\n"
            . "Bonne nouvelle ! La réparation de votre {$device->brand} {$device->model} (Ticket {$ticket->reference}) est terminée avec succès.\n"
            . "Votre appareil a passé les tests de contrôle et est prêt à être récupéré à notre atelier.\n"
            . ($tenant->address ? "Adresse : {$tenant->address}\n" : "")
            . $balanceText . "\n"
            . "Horaires d'ouverture habituels.\n"
            . "— {$tenant->name} (" . ($tenant->phone ?: '') . ")";
    }

    /**
     * Generate WhatsApp Click-To-Chat URL.
     */
    public function generateWhatsAppUrl(string $phone, string $message, string $defaultCountryCode = '226'): string
    {
        $cleanPhone = $this->cleanPhoneNumber($phone, $defaultCountryCode);
        $encodedText = rawurlencode($message);

        return "https://wa.me/{$cleanPhone}?text={$encodedText}";
    }
}
