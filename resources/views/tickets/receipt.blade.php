<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Bon de Dépôt — {{ $ticket->reference }}</title>
    <style>
        @page { size: A4; margin: 15mm; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; font-size: 13px; line-height: 1.5; color: #1e293b; background: #fff; margin: 0; padding: 20px; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #2563eb; padding-bottom: 12px; margin-bottom: 20px; }
        .brand { font-size: 22px; font-weight: bold; color: #0f172a; }
        .brand span { color: #2563eb; }
        .doc-title { text-align: right; }
        .doc-title h1 { margin: 0; font-size: 18px; color: #1e293b; text-transform: uppercase; }
        .ref { font-family: monospace; font-size: 16px; font-weight: bold; color: #2563eb; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; }
        .card h3 { margin: 0 0 8px 0; font-size: 11px; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; }
        .row { display: flex; justify-content: space-between; margin-bottom: 4px; }
        .label { color: #64748b; }
        .val { font-weight: 600; color: #0f172a; }
        .section-title { font-size: 12px; font-weight: bold; text-transform: uppercase; color: #0f172a; margin: 16px 0 8px 0; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; }
        .desc-box { background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px; min-height: 40px; margin-bottom: 12px; }
        .tracking-box { background: #eff6ff; border: 1px dashed #3b82f6; border-radius: 8px; padding: 12px; text-align: center; margin: 20px 0; font-size: 12px; }
        .terms { font-size: 10px; color: #64748b; text-align: justify; border-top: 1px solid #e2e8f0; padding-top: 10px; margin-top: 25px; line-height: 1.4; }
        .signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 30px; }
        .sig-box { border-top: 1px solid #94a3b8; padding-top: 6px; text-align: center; font-size: 11px; color: #475569; min-height: 60px; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #2563eb; color: #fff; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">
            Imprimer ce bon de dépôt
        </button>
    </div>

    <!-- Header Atelier -->
    <div class="header">
        <div>
            <div class="brand">{{ $ticket->tenant->name }}</div>
            <div style="color: #64748b; font-size: 12px; margin-top: 3px;">
                {{ $ticket->tenant->address ?: 'Maintenance & Dépannage Informatique' }}<br>
                {{ $ticket->tenant->city }} ({{ $ticket->tenant->country_code }}) &bull; Tél : {{ $ticket->tenant->phone ?: 'N/A' }}
            </div>
        </div>
        <div class="doc-title">
            <h1>Bon de Prise en Charge</h1>
            <div class="ref">{{ $ticket->reference }}</div>
            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                Dépôt le {{ $ticket->deposit_date->format('d/m/Y à H:i') }}
            </div>
        </div>
    </div>

    <!-- Info Client & Équipement -->
    <div class="grid">
        <div class="card">
            <h3>Informations Client</h3>
            <div class="row"><span class="label">Nom :</span> <span class="val">{{ $ticket->customer->name }}</span></div>
            <div class="row"><span class="label">Téléphone :</span> <span class="val">{{ $ticket->customer->phone }}</span></div>
            <div class="row"><span class="label">Adresse :</span> <span class="val">{{ $ticket->customer->address ?: 'Non précisée' }}</span></div>
        </div>

        <div class="card">
            <h3>Appareil Déposé</h3>
            <div class="row"><span class="label">Type :</span> <span class="val">{{ $ticket->device->category_label }}</span></div>
            <div class="row"><span class="label">Marque & Modèle :</span> <span class="val">{{ $ticket->device->brand }} {{ $ticket->device->model }}</span></div>
            <div class="row"><span class="label">N° de Série :</span> <span class="val">{{ $ticket->device->serial_number ?: 'N/A' }}</span></div>
        </div>
    </div>

    <!-- Détails Réception -->
    <div class="section-title">Motif du Dépôt & Panne Signalée</div>
    <div class="desc-box">
        <strong>Problème déclaré :</strong> {{ $ticket->reported_issue }}
    </div>

    <div class="grid">
        <div>
            <div class="section-title">État Constaté à l'Entrée</div>
            <div class="desc-box">
                {{ $ticket->intake_condition ?: 'Bon état d’aspect général constaté à l’entrée.' }}
            </div>
        </div>
        <div>
            <div class="section-title">Accessoires Reçus</div>
            <div class="desc-box">
                {{ $ticket->accessories_received ?: 'Aucun accessoire déposé.' }}
            </div>
        </div>
    </div>

    <!-- Encadré Suivi Client en ligne -->
    <div class="tracking-box">
        <strong style="color: #1d4ed8;">Suivez l'avancement de votre réparation en temps réel :</strong><br>
        Lien public de consultation : <span style="font-family: monospace; font-weight: bold;">{{ url("/suivi/{$ticket->public_tracking_token}") }}</span>
    </div>

    <!-- Conditions Générales -->
    <div class="terms">
        <strong>Conditions Générales de Dépôt :</strong>
        1. Le présent document constitue la preuve de dépôt de votre équipement et devra obligatoirement être présenté lors du retrait.
        2. Sauvegarde des données : L'atelier n'est en aucun cas responsable des pertes de données personnelles ou logicielles survenues pendant l'intervention technique. Le client est tenu d'effectuer ses sauvegardes préalables.
        3. Délai de garde : Tout équipement réparé et non réclamé après un délai de 60 jours à compter de la notification de mise à disposition fera l'objet de frais de gardiennage ou sera cédé pour couvrir les frais de réparation conformément à la réglementation en vigueur.
    </div>

    <!-- Signatures -->
    <div class="signatures">
        <div class="sig-box">
            Signature du Client<br>
            <span style="font-size: 10px; color: #94a3b8;">(Mention manuscrite « Bon pour accord »)</span>
        </div>
        <div class="sig-box">
            Cachet & Signature de l'Atelier<br>
            <span style="font-size: 10px; color: #94a3b8;">{{ $ticket->tenant->name }}</span>
        </div>
    </div>
</body>
</html>
