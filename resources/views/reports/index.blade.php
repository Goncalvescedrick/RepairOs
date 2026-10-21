<x-app-layout title="Rapports d'activité & Audit">
    <div class="space-y-6">
        <!-- Header & Date filter -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Rapports d'activité & Journal d'Audit</h1>
                <p class="text-sm text-slate-500 mt-1">Consultez les performances de l'atelier, les flux financiers et l'historique des opérations sensibles.</p>
            </div>

            <!-- Date range form -->
            <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-center gap-2 bg-white p-2 rounded-2xl border border-slate-200 shadow-sm text-xs">
                <div class="flex items-center space-x-1 px-2">
                    <span class="text-slate-400 font-medium">Du</span>
                    <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" class="px-2 py-1.5 border border-slate-200 rounded-lg text-slate-700 font-medium focus:ring-1 focus:ring-blue-500">
                </div>
                <div class="flex items-center space-x-1 px-2">
                    <span class="text-slate-400 font-medium">Au</span>
                    <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" class="px-2 py-1.5 border border-slate-200 rounded-lg text-slate-700 font-medium focus:ring-1 focus:ring-blue-500">
                </div>
                <button type="submit" class="px-3.5 py-1.5 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transition-colors">
                    Filtrer
                </button>
            </form>
        </div>

        <!-- Financial Overview KPIs -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Chiffre d'Affaires Facturé</span>
                <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($totalInvoiced, 0, ',', ' ') }} {{ $tenant->currency }}</div>
                <div class="text-xs text-slate-500 mt-1">{{ $invoicesCount }} facture(s) émise(s) sur la période</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-indigo-100 bg-indigo-50/20 shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-indigo-700">Marge Brute Estimée</span>
                <div class="text-2xl font-extrabold text-indigo-700 mt-1">{{ number_format($estimatedGrossMargin ?? $totalInvoiced, 0, ',', ' ') }} {{ $tenant->currency }}</div>
                <div class="text-xs text-indigo-700/80 mt-1">CA net hors coût d'achat des pièces ({{ number_format($totalPartsCost ?? 0, 0, ',', ' ') }})</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-emerald-100 bg-emerald-50/20 shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Règlements Encaissés</span>
                <div class="text-2xl font-extrabold text-emerald-600 mt-1">{{ number_format($totalCollected, 0, ',', ' ') }} {{ $tenant->currency }}</div>
                <div class="text-xs text-emerald-700/80 mt-1">{{ $paymentsCount }} versement(s) effectif(s) reçu(s)</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-amber-100 bg-amber-50/20 shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-amber-700">Créances à Recouvrer (Global)</span>
                <div class="text-2xl font-extrabold text-amber-600 mt-1">{{ number_format($totalBalanceDue, 0, ',', ' ') }} {{ $tenant->currency }}</div>
                <div class="text-xs text-amber-700/80 mt-1">Total des soldes restants sur factures actives</div>
            </div>
        </div>

        <!-- Operational KPIs -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tickets Enregistrés</span>
                <div class="text-2xl font-extrabold text-blue-600 mt-1">{{ $ticketsCreatedCount }}</div>
                <div class="text-xs text-slate-500 mt-1">Appareils déposés sur la période</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Réparations Restituées</span>
                <div class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $ticketsClosedCount }}</div>
                <div class="text-xs text-slate-500 mt-1">Dossiers clôturés avec remise au client</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Délai Moyen de Traitement</span>
                <div class="text-2xl font-extrabold text-purple-600 mt-1">
                    {{ $averageTurnaroundDays !== null ? $averageTurnaroundDays . ' jours' : 'N/A' }}
                </div>
                <div class="text-xs text-slate-500 mt-1">Temps moyen entre le dépôt et la clôture</div>
            </div>
        </div>

        <!-- Status Breakdown Grid -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-900">État Actuel du Parc d'Interventions</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
                @foreach($statusCounts as $code => $data)
                    <div class="p-3.5 rounded-xl border border-slate-100 bg-slate-50 text-center">
                        <span class="text-[11px] font-medium text-slate-500 block truncate">{{ $data['label'] }}</span>
                        <span class="text-xl font-bold text-slate-900 mt-1 block">{{ $data['count'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Technician Performance Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="font-bold text-slate-900 text-sm">Activité des Techniciens sur la Période</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider font-semibold text-slate-500 border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-3.5">Technicien</th>
                            <th class="px-6 py-3.5 text-center">Tickets Assignés</th>
                            <th class="px-6 py-3.5 text-center">Interventions Enregistrées</th>
                            <th class="px-6 py-3.5 text-center">Dossiers Restitués</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($technicians as $tech)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">{{ $tech['name'] }}</div>
                                    <div class="text-xs text-slate-400">{{ $tech['email'] }}</div>
                                </td>
                                <td class="px-6 py-4 text-center font-bold text-slate-800">{{ $tech['assigned'] }}</td>
                                <td class="px-6 py-4 text-center font-bold text-blue-600">{{ $tech['interventions'] }}</td>
                                <td class="px-6 py-4 text-center font-bold text-emerald-600">{{ $tech['completed'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-slate-400">Aucun technicien enregistré.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Audit Trail Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Journal d'Audit des Événements Sensibles</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Traces immuables des créations, modifications de statuts, factures et paiements.</p>
                </div>
                <span class="text-xs font-mono text-slate-400">30 derniers événements</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[10px] uppercase tracking-wider font-semibold text-slate-500 border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-3">Date & Heure</th>
                            <th class="px-6 py-3">Auteur</th>
                            <th class="px-6 py-3">Événement</th>
                            <th class="px-6 py-3">Objet</th>
                            <th class="px-6 py-3">Adresse IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-mono">
                        @forelse($auditLogs as $log)
                            <tr class="hover:bg-slate-50/60">
                                <td class="px-6 py-3 text-slate-500 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                <td class="px-6 py-3 font-sans font-medium text-slate-900">{{ $log->user?->name ?? 'Système' }}</td>
                                <td class="px-6 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-800">
                                        {{ $log->event }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-slate-500 truncate max-w-xs">
                                    {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                                </td>
                                <td class="px-6 py-3 text-slate-400">{{ $log->ip_address ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-slate-400 font-sans">Aucun événement d'audit consigné pour l'instant.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
