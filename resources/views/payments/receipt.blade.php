<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Reçu de Paiement — {{ $payment->reference }}</title>
    <style>
        @page { size: A4; margin: 15mm; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; font-size: 13px; line-height: 1.5; color: #1e293b; background: #fff; margin: 0; padding: 20px; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #16a34a; padding-bottom: 12px; margin-bottom: 20px; }
        .brand { font-size: 22px; font-weight: bold; color: #0f172a; }
        .doc-title { text-align: right; }
        .doc-title h1 { margin: 0; font-size: 18px; color: #1e293b; text-transform: uppercase; }
        .ref { font-family: monospace; font-size: 16px; font-weight: bold; color: #16a34a; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; }
        .card h3 { margin: 0 0 10px 0; font-size: 11px; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; }
        .row { display: flex; justify-content: space-between; margin-bottom: 6px; }
        .label { color: #64748b; }
        .val { font-weight: 600; color: #0f172a; }
        .cancelled-banner { background: #fef2f2; border: 2px dashed #ef4444; color: #b91c1c; padding: 12px; border-radius: 8px; text-align: center; font-weight: bold; margin-bottom: 20px; font-size: 14px; }
        .payment-box { background: #f0fdf4; border: 2px solid #bbf7d0; border-radius: 8px; padding: 16px; margin-bottom: 20px; text-align: center; }
        .payment-amount { font-size: 26px; font-weight: 800; color: #15803d; }
        .payment-words { font-size: 12px; color: #475569; margin-top: 4px; font-style: italic; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #f1f5f9; text-align: left; padding: 8px 10px; font-size: 11px; text-transform: uppercase; color: #475569; border-bottom: 1px solid #cbd5e1; }
        td { padding: 10px; border-bottom: 1px solid #e2e8f0; }
        .signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 30px; }
        .sig-box { border-top: 1px solid #94a3b8; padding-top: 6px; text-align: center; font-size: 11px; color: #475569; min-height: 60px; }
        .footer-note { font-size: 10px; color: #64748b; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 12px; margin-top: 30px; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
        <a href="{{ route('invoices.show', $payment->invoice) }}" style="color: #64748b; text-decoration: none; font-size: 13px;">&larr; Retour à la facture</a>
        <button onclick="window.print()" style="padding: 8px 16px; background: #16a34a; color: #fff; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">
            Imprimer ce reçu
        </button>
    </div>

    @if($payment->is_cancelled)
        <div class="cancelled-banner">
            DOCUMENT ANNULÉ — Ce règlement a été annulé.<br>
            <span style="font-size: 12px; font-weight: normal;">Motif : {{ $payment->cancellation_reason }}</span>
        </div>
    @endif

    <!-- Header Atelier -->
    <div class="header">
        <div>
            <div class="brand">{{ $payment->tenant->name }}</div>
            <div style="color: #64748b; font-size: 12px; margin-top: 3px;">
                {{ $payment->tenant->address ?: 'Maintenance & Dépannage Informatique' }}<br>
                {{ $payment->tenant->city }} ({{ $payment->tenant->country_code }}) &bull; Tél : {{ $payment->tenant->phone ?: 'N/A' }}
            </div>
        </div>
        <div class="doc-title">
            <h1>Reçu de Paiement</h1>
            <div class="ref">{{ $payment->reference }}</div>
            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                Émis le {{ $payment->paid_at->format('d/m/Y à H:i') }}
            </div>
        </div>
    </div>

    <!-- Info Client & Renseignements -->
    <div class="grid">
        <div class="card">
            <h3>Bénéficiaire / Client</h3>
            <div class="row">
                <span class="label">Nom :</span>
                <span class="val">{{ $payment->invoice->customer->name }}</span>
            </div>
            <div class="row">
                <span class="label">Téléphone :</span>
                <span class="val">{{ $payment->invoice->customer->phone }}</span>
            </div>
            @if($payment->invoice->customer->email)
                <div class="row">
                    <span class="label">Email :</span>
                    <span class="val">{{ $payment->invoice->customer->email }}</span>
                </div>
            @endif
        </div>

        <div class="card">
            <h3>Détails du Règlement</h3>
            <div class="row">
                <span class="label">Mode de règlement :</span>
                <span class="val">{{ $payment->payment_method->label() }}</span>
            </div>
            @if($payment->transaction_reference)
                <div class="row">
                    <span class="label">Réf. transaction :</span>
                    <span class="val" style="font-family: monospace;">{{ $payment->transaction_reference }}</span>
                </div>
            @endif
            <div class="row">
                <span class="label">Encaissé par :</span>
                <span class="val">{{ $payment->user?->name ?? 'Système' }}</span>
            </div>
            <div class="row">
                <span class="label">Facture liée :</span>
                <span class="val" style="font-family: monospace;">{{ $payment->invoice->reference }}</span>
            </div>
            @if($payment->invoice->ticket)
                <div class="row">
                    <span class="label">Ticket lié :</span>
                    <span class="val">{{ $payment->invoice->ticket->reference }} ({{ $payment->invoice->ticket->device->brand }} {{ $payment->invoice->ticket->device->model }})</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Bloc Montant Payé -->
    <div class="payment-box">
        <div style="font-size: 12px; text-transform: uppercase; color: #166534; font-weight: 700; letter-spacing: 0.5px;">Montant Total Encaissé</div>
        <div class="payment-amount">{{ number_format($payment->amount, 0, ',', ' ') }} {{ $payment->invoice->currency }}</div>
        @if($payment->notes)
            <div class="payment-words">Note : {{ $payment->notes }}</div>
        @endif
    </div>

    <!-- Récapitulatif Facture -->
    <div class="card" style="margin-bottom: 20px;">
        <h3>Situation du compte après ce versement</h3>
        <div class="row">
            <span class="label">Montant total de la facture ({{ $payment->invoice->reference }}) :</span>
            <span class="val">{{ number_format($payment->invoice->total_amount, 0, ',', ' ') }} {{ $payment->invoice->currency }}</span>
        </div>
        <div class="row">
            <span class="label">Total cumulé des règlements perçus :</span>
            <span class="val" style="color: #16a34a;">{{ number_format($payment->invoice->paid_amount, 0, ',', ' ') }} {{ $payment->invoice->currency }}</span>
        </div>
        <div class="row" style="border-top: 1px solid #e2e8f0; padding-top: 6px; margin-top: 6px;">
            <span class="label" style="font-weight: 700; color: #0f172a;">Solde restant à devoir :</span>
            <span class="val" style="font-weight: 700; font-size: 15px; color: {{ $payment->invoice->balance_due > 0 ? '#b91c1c' : '#15803d' }};">
                {{ number_format($payment->invoice->balance_due, 0, ',', ' ') }} {{ $payment->invoice->currency }}
                @if($payment->invoice->balance_due <= 0)
                    (Soldé)
                @endif
            </span>
        </div>
    </div>

    <!-- Signatures -->
    <div class="signatures">
        <div class="sig-box">
            Le Client (Bon pour accord & règlement)
        </div>
        <div class="sig-box">
            Cachet & Signature de l'Atelier
        </div>
    </div>

    <div class="footer-note">
        Ce reçu certifie le paiement effectif de la somme indiquée ci-dessus.<br>
        {{ $payment->tenant->name }} &bull; Logiciel de gestion professionnelle RepairOS
    </div>
</body>
</html>
