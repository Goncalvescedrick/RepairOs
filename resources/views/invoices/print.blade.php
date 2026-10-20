<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Facture — {{ $invoice->reference }}</title>
    <style>
        @page { size: A4; margin: 15mm; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; font-size: 13px; line-height: 1.5; color: #1e293b; background: #fff; margin: 0; padding: 20px; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #2563eb; padding-bottom: 15px; margin-bottom: 20px; }
        .brand { font-size: 24px; font-weight: bold; color: #0f172a; }
        .doc-title { text-align: right; }
        .doc-title h1 { margin: 0; font-size: 20px; color: #1e293b; text-transform: uppercase; }
        .ref { font-family: monospace; font-size: 16px; font-weight: bold; color: #2563eb; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; }
        .card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; font-size: 12px; }
        .card h3 { margin: 0 0 8px 0; font-size: 11px; text-transform: uppercase; color: #64748b; }
        .row { display: flex; justify-content: space-between; margin-bottom: 3px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 12px; }
        th { background: #f1f5f9; padding: 8px 10px; border-bottom: 2px solid #cbd5e1; text-align: left; text-transform: uppercase; font-size: 11px; color: #475569; }
        td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; }
        .text-right { text-align: right; }
        .totals { width: 320px; margin-left: auto; font-size: 12px; }
        .totals .row { padding: 4px 0; }
        .total-ttc { font-size: 15px; font-weight: bold; color: #0f172a; border-top: 2px solid #0f172a; padding-top: 6px; }
        .paid { color: #15803d; font-weight: 600; }
        .due { color: #b45309; font-weight: bold; font-size: 14px; border-top: 1px solid #e2e8f0; padding-top: 6px; }
        .signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 40px; }
        .sig-box { border-top: 1px solid #94a3b8; padding-top: 6px; text-align: center; font-size: 11px; color: #475569; min-height: 70px; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #2563eb; color: #fff; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">
            Imprimer cette facture
        </button>
    </div>

    <div class="header">
        <div>
            <div class="brand">{{ $invoice->tenant->name }}</div>
            <div style="color: #64748b; font-size: 12px; margin-top: 3px;">
                {{ $invoice->tenant->address ?: 'Atelier de Maintenance & Réparations' }}<br>
                {{ $invoice->tenant->city }} ({{ $invoice->tenant->country_code }}) &bull; Tél : {{ $invoice->tenant->phone }}
            </div>
        </div>
        <div class="doc-title">
            <h1>Facture</h1>
            <div class="ref">{{ $invoice->reference }}</div>
            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                Date d'émission : {{ $invoice->issued_at->format('d/m/Y') }}<br>
                @if($invoice->due_date) Échéance : {{ $invoice->due_date->format('d/m/Y') }} @endif
            </div>
        </div>
    </div>

    <div class="grid">
        <div class="card">
            <h3>Client (Facturé à)</h3>
            <div class="row"><strong>{{ $invoice->customer->name }}</strong></div>
            <div class="row">Téléphone : {{ $invoice->customer->phone }}</div>
            <div class="row">Adresse : {{ $invoice->customer->address ?: 'Non renseignée' }}</div>
        </div>
        <div class="card">
            <h3>Détails Réparation</h3>
            <div class="row">Réf Ticket : <strong>{{ $invoice->ticket->reference }}</strong></div>
            <div class="row">Équipement : {{ $invoice->ticket->device->brand }} {{ $invoice->ticket->device->model }}</div>
            <div class="row">N° Série : {{ $invoice->ticket->device->serial_number ?: 'N/A' }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Désignation</th>
                <th style="text-align: center; width: 60px;">Qté</th>
                <th class="text-right" style="width: 100px;">Prix Unit.</th>
                <th class="text-right" style="width: 110px;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td>{{ $item->type === 'SERVICE' ? 'Service' : 'Pièce' }}</td>
                <td><strong>{{ $item->description }}</strong></td>
                <td style="text-align: center;">{{ number_format($item->quantity, 2) }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 0, ',', ' ') }} {{ $invoice->currency }}</td>
                <td class="text-right">{{ number_format($item->total_price, 0, ',', ' ') }} {{ $invoice->currency }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="row"><span>Total HT :</span> <span>{{ number_format($invoice->subtotal_amount, 0, ',', ' ') }} {{ $invoice->currency }}</span></div>
        @if((float) $invoice->discount_amount > 0)
        <div class="row" style="color: #15803d;"><span>Remise :</span> <span>- {{ number_format($invoice->discount_amount, 0, ',', ' ') }} {{ $invoice->currency }}</span></div>
        @endif
        @if((float) $invoice->tax_rate > 0)
        <div class="row"><span>TVA ({{ $invoice->tax_rate }}%) :</span> <span>{{ number_format($invoice->tax_amount, 0, ',', ' ') }} {{ $invoice->currency }}</span></div>
        @endif
        <div class="row total-ttc"><span>Net Facturé TTC :</span> <span>{{ number_format($invoice->total_amount, 0, ',', ' ') }} {{ $invoice->currency }}</span></div>
        <div class="row paid"><span>Montant Réglé :</span> <span>{{ number_format($invoice->paid_amount, 0, ',', ' ') }} {{ $invoice->currency }}</span></div>
        <div class="row due"><span>Solde Restant Dû :</span> <span>{{ number_format($invoice->balance_due, 0, ',', ' ') }} {{ $invoice->currency }}</span></div>
    </div>

    @if($invoice->notes)
    <div style="font-size: 11px; color: #475569; margin-top: 20px; background: #f8fafc; padding: 10px; border-radius: 6px;">
        <strong>Modalités :</strong> {{ $invoice->notes }}
    </div>
    @endif

    <div class="signatures">
        <div class="sig-box">
            Le Client<br>
            <span style="font-size: 10px; color: #94a3b8;">(Accusé de réception)</span>
        </div>
        <div class="sig-box">
            Cachet & Signature de l'Atelier<br>
            <span style="font-size: 10px; color: #94a3b8;">{{ $invoice->tenant->name }}</span>
        </div>
    </div>
</body>
</html>
