<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Étiquette Atelier — {{ $ticket->reference }}</title>
    <style>
        @page { size: 60mm 40mm; margin: 2mm; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            margin: 0;
            padding: 8px;
            background: #fff;
            color: #0f172a;
        }
        .label-box {
            width: 56mm;
            border: 1.5px solid #0f172a;
            border-radius: 4px;
            padding: 6px;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
        }
        .info {
            flex: 1;
            min-width: 0;
        }
        .workshop {
            font-size: 8px;
            text-transform: uppercase;
            font-weight: 700;
            color: #475569;
            letter-spacing: 0.4px;
        }
        .ref {
            font-family: monospace;
            font-size: 13px;
            font-weight: 900;
            color: #0f172a;
            margin: 1px 0;
        }
        .line {
            font-size: 9px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sub {
            font-size: 8px;
            color: #334155;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .qr {
            flex-shrink: 0;
            text-align: center;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 12px;">
        <button onclick="window.print()" style="padding: 6px 12px; background: #2563eb; color: #fff; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer;">
            Imprimer l'étiquette autocollante (60x40mm)
        </button>
    </div>

    <div class="label-box">
        <div class="info">
            <div class="workshop">{{ $ticket->tenant->name }}</div>
            <div class="ref">{{ $ticket->reference }}</div>
            <div class="line">{{ $ticket->customer->name }}</div>
            <div class="sub">{{ $ticket->customer->phone }}</div>
            <div class="line" style="margin-top: 2px;">{{ $ticket->device->brand }} {{ $ticket->device->model }}</div>
            <div class="sub">Dépôt : {{ $ticket->deposit_date->format('d/m/Y') }} &bull; {{ $ticket->priority->value }}</div>
        </div>
        <div class="qr">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data={{ urlencode(url("/suivi/{$ticket->public_tracking_token}")) }}"
                 alt="QR" width="56" height="56">
        </div>
    </div>
</body>
</html>
