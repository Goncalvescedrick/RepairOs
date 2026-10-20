<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Devis — {{ $quote->reference }}</title>
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
        .totals { width: 280px; margin-left: auto; font-size: 12px; }
        .totals .row { padding: 4px 0; }
        .total-ttc { font-size: 15px; font-weight: bold; color: #1d4ed8; border-top: 2px solid #0f172a; padding-top: 6px; }
        .signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 50px; }
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
            Imprimer ce devis
        </button>
    </div>

    <div class="header">
        <div>
            <div class="brand">{{ $quote->tenant->name }}</div>
            <div style="color: #64748b; font-size: 12px; margin-top: 3px;">
                {{ $quote->tenant->address ?: 'Atelier de Maintenance & Réparation' }}<br>
                {{ $quote->tenant->city }} ({{ $quote->tenant->country_code }}) &bull; Tél : {{ $quote->tenant->phone }}
            </div>
        </div>
        <div class="doc-title">
            <h1>Devis Estimatif</h1>
            <div class="ref">{{ $quote->reference }}</div>
            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                Date d'émission : {{ $quote->created_at->format('d/m/Y') }}<br>
                Validité : {{ $quote->valid_until ? $quote->valid_until->format('d/m/Y') : '15 jours' }}
            </div>
        </div>
    </div>

    <div class="grid">
        <div class="card">
            <h3>Destinataire (Client)</h3>
            <div class="row"><strong>{{ $quote->customer->name }}</strong></div>
            <div class="row">Téléphone : {{ $quote->customer->phone }}</div>
            <div class="row">Adresse : {{ $quote->customer->address ?: 'Non renseignée' }}</div>
        </div>
        <div class="card">
            <h3>Appareil & Ticket</h3>
            <div class="row">Réf Ticket : <strong>{{ $quote->ticket->reference }}</strong></div>
            <div class="row">Équipement : {{ $quote->ticket->device->brand }} {{ $quote->ticket->device->model }}</div>
            <div class="row">N° Série : {{ $quote->ticket->device->serial_number ?: 'N/A' }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Désignation de la prestation / Pièce</th>
                <th style="text-align: center; width: 60px;">Qté</th>
                <th class="text-right" style="width: 100px;">Prix Unit.</th>
                <th class="text-right" style="width: 110px;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quote->items as $item)
            <tr>
                <td>{{ $item->type === 'SERVICE' ? 'Service' : 'Pièce' }}</td>
                <td><strong>{{ $item->description }}</strong></td>
                <td style="text-align: center;">{{ number_format($item->quantity, 2) }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 0, ',', ' ') }} {{ $quote->currency }}</td>
                <td class="text-right">{{ number_format($item->total_price, 0, ',', ' ') }} {{ $quote->currency }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="row"><span>Sous-total HT :</span> <span>{{ number_format($quote->subtotal_amount, 0, ',', ' ') }} {{ $quote->currency }}</span></div>
        @if((float) $quote->discount_amount > 0)
        <div class="row" style="color: #15803d;"><span>Remise :</span> <span>- {{ number_format($quote->discount_amount, 0, ',', ' ') }} {{ $quote->currency }}</span></div>
        @endif
        @if((float) $quote->tax_rate > 0)
        <div class="row"><span>TVA ({{ $quote->tax_rate }}%) :</span> <span>{{ number_format($quote->tax_amount, 0, ',', ' ') }} {{ $quote->currency }}</span></div>
        @endif
        <div class="row total-ttc"><span>Net à Payer TTC :</span> <span>{{ number_format($quote->total_amount, 0, ',', ' ') }} {{ $quote->currency }}</span></div>
    </div>

    @if($quote->notes)
    <div style="font-size: 11px; color: #475569; margin-top: 20px; background: #f8fafc; padding: 10px; border-radius: 6px;">
        <strong>Remarques :</strong> {{ $quote->notes }}
    </div>
    @endif

    <div class="signatures">
        <div class="sig-box">
            Bon pour accord le client<br>
            <span style="font-size: 10px; color: #94a3b8;">(Date et signature)</span>
        </div>
        <div class="sig-box">
            Le Responsable d'Atelier<br>
            <span style="font-size: 10px; color: #94a3b8;">{{ $quote->tenant->name }}</span>
        </div>
    </div>
</body>
</html>
