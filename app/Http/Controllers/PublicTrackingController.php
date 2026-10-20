<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\RepairTicket;
use App\Scopes\TenantScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PublicTrackingController extends Controller
{
    public function show(string $token): View
    {
        // Enforce token format security: 32-128 alphanumeric characters
        if (! preg_match('/^[a-zA-Z0-9_\-]{32,128}$/', $token)) {
            abort(404, 'Lien de suivi invalide ou expiré.');
        }

        // Must bypass TenantScope because public customer has no active tenant session
        $ticket = RepairTicket::withoutGlobalScope(TenantScope::class)
            ->where('public_tracking_token', $token)
            ->with([
                'tenant',
                'device',
                'customer',
                'statusHistories',
                'interventions' => function ($q) {
                    $q->whereNotNull('customer_summary')->where('customer_summary', '!=', '');
                },
                'attachments' => function ($q) {
                    $q->where('is_public_to_client', true);
                },
            ])
            ->first();

        if (! $ticket) {
            abort(404, 'Dossier de réparation introuvable avec ce lien de suivi.');
        }

        // Stepper status logic for public visual progress
        $statusKey = $ticket->status->value;
        $progressPercent = match ($statusKey) {
            'RECU' => 15,
            'DIAGNOSTIC' => 35,
            'DEVIS_EN_ATTENTE', 'EN_ATTENTE_CLIENT' => 50,
            'EN_REPARATION' => 70,
            'TEST' => 85,
            'PRET_A_RETIRER' => 95,
            'RESTITUE' => 100,
            'ANNULE' => 0,
            default => 20,
        };

        return view('public.tracking', compact('ticket', 'progressPercent'));
    }

    public function attachment(string $token, Attachment $attachment): BinaryFileResponse
    {
        // Must bypass TenantScope
        $ticket = RepairTicket::withoutGlobalScope(TenantScope::class)
            ->where('public_tracking_token', $token)
            ->firstOrFail();

        // Verify attachment belongs to this ticket AND is flagged as public to client
        if (
            $attachment->attachable_type !== RepairTicket::class ||
            $attachment->attachable_id !== $ticket->id ||
            ! $attachment->is_public_to_client
        ) {
            abort(403, 'Document non accessible publiquement.');
        }

        if (! Storage::disk('local')->exists($attachment->storage_path)) {
            abort(404, 'Fichier introuvable sur le disque.');
        }

        $fullPath = Storage::disk('local')->path($attachment->storage_path);

        return response()->file($fullPath, [
            'Content-Type' => $attachment->mime_type,
            'Content-Disposition' => 'inline; filename="' . addslashes($attachment->original_filename) . '"',
        ]);
    }
}
