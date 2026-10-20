<x-layouts.app>
    <x-slot:title>Tableau de bord</x-slot:title>

    <div class="space-y-6">
        <!-- En-tête du Dashboard -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Tableau de bord Atelier</h1>
                <p class="text-sm text-slate-500 mt-0.5">Vue d'ensemble de l'activité technique et financière de {{ $tenant->name }}</p>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('tickets.create') }}" class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Enregistrer une réparation
                </a>
            </div>
        </div>

        <!-- Grille des Indicateurs Métier (KPIs) -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Tickets ouverts -->
            <a href="{{ route('tickets.index') }}" class="p-4 bg-white rounded-xl border border-slate-200 shadow-xs hover:border-blue-300 hover:shadow-sm transition group">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-semibold uppercase tracking-wider">Tickets en cours</span>
                    <span class="p-2 bg-blue-50 text-blue-600 rounded-lg group-hover:bg-blue-600 group-hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </span>
                </div>
                <div class="text-2xl font-bold text-slate-900 mt-2">{{ $ticketsOpenCount }}</div>
                <div class="text-[11px] text-slate-400 mt-1">Total réparations non clôturées</div>
            </a>

            <!-- En diagnostic -->
            <a href="{{ route('tickets.index', ['status' => 'DIAGNOSTIC']) }}" class="p-4 bg-white rounded-xl border border-slate-200 shadow-xs hover:border-blue-300 hover:shadow-sm transition group">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-semibold uppercase tracking-wider">En diagnostic</span>
                    <span class="p-2 bg-indigo-50 text-indigo-600 rounded-lg group-hover:bg-indigo-600 group-hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                </div>
                <div class="text-2xl font-bold text-slate-900 mt-2">{{ $inDiagnosticCount }}</div>
                <div class="text-[11px] text-slate-400 mt-1">Examens techniques à faire</div>
            </a>

            <!-- Prêts à retirer -->
            <a href="{{ route('tickets.index', ['status' => 'PRET_A_RETIRER']) }}" class="p-4 bg-white rounded-xl border border-slate-200 shadow-xs hover:border-emerald-300 hover:shadow-sm transition group">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-semibold uppercase tracking-wider">Prêts à retirer</span>
                    <span class="p-2 bg-emerald-50 text-emerald-600 rounded-lg group-hover:bg-emerald-600 group-hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </span>
                </div>
                <div class="text-2xl font-bold text-emerald-700 mt-2">{{ $readyForPickupCount }}</div>
                <div class="text-[11px] text-slate-400 mt-1">Clients à contacter pour retrait</div>
            </a>

            <!-- Tickets en retard -->
            <a href="{{ route('tickets.index', ['overdue' => 1]) }}" class="p-4 bg-white rounded-xl border border-slate-200 shadow-xs hover:border-rose-300 hover:shadow-sm transition group">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-semibold uppercase tracking-wider">En retard</span>
                    <span class="p-2 bg-rose-50 text-rose-600 rounded-lg group-hover:bg-rose-600 group-hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <div class="text-2xl font-bold text-rose-700 mt-2">{{ $overdueTicketsCount }}</div>
                <div class="text-[11px] text-slate-400 mt-1">Date estimée dépassée</div>
            </a>
        </div>

        <!-- Grille Financière -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Paiements reçus ce mois -->
            <div class="p-5 bg-gradient-to-br from-slate-900 to-slate-800 text-white rounded-2xl shadow-sm border border-slate-800">
                <div class="flex items-center justify-between text-slate-400 text-xs font-semibold uppercase tracking-wider">
                    <span>Recettes ce mois-ci</span>
                    <span class="px-2 py-0.5 bg-emerald-900/60 text-emerald-400 rounded text-[10px] font-bold">Encaissé réel</span>
                </div>
                <div class="text-3xl font-extrabold mt-3 tracking-tight">
                    {{ number_format($paymentsThisMonth, 0, ',', ' ') }} <span class="text-base font-medium text-slate-300">{{ $currency }}</span>
                </div>
                <div class="text-xs text-slate-400 mt-2 flex items-center">
                    <svg class="w-4 h-4 mr-1 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Paiements confirmés (Espèces & Mobile Money)
                </div>
            </div>

            <!-- Soldes restant à recouvrer -->
            <div class="p-5 bg-white rounded-2xl shadow-xs border border-slate-200">
                <div class="flex items-center justify-between text-slate-500 text-xs font-semibold uppercase tracking-wider">
                    <span>Reste à recouvrer</span>
                    <span class="px-2 py-0.5 bg-amber-50 text-amber-800 rounded text-[10px] font-bold border border-amber-200">Créances</span>
                </div>
                <div class="text-3xl font-extrabold text-amber-700 mt-3 tracking-tight">
                    {{ number_format($totalOutstandingBalance, 0, ',', ' ') }} <span class="text-base font-medium text-slate-500">{{ $currency }}</span>
                </div>
                <div class="text-xs text-slate-500 mt-2">
                    Montant total restant dû sur les factures émises
                </div>
            </div>

            <!-- Devis en attente -->
            <div class="p-5 bg-white rounded-2xl shadow-xs border border-slate-200 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-slate-500 text-xs font-semibold uppercase tracking-wider">
                        <span>Devis en négociation</span>
                        <span class="px-2 py-0.5 bg-blue-50 text-blue-800 rounded text-[10px] font-bold border border-blue-200">Brouillon / Envoyé</span>
                    </div>
                    <div class="text-3xl font-extrabold text-slate-900 mt-3 tracking-tight">
                        {{ $quotesPendingCount }}
                    </div>
                </div>
                <a href="{{ route('quotes.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 inline-flex items-center mt-2">
                    Voir les devis en attente d'accord &rarr;
                </a>
            </div>
        </div>

        <!-- Section Tableau Récentes Réparations & Historique d'Activité -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Liste des derniers tickets (2 colonnes) -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Derniers tickets enregistrés</h2>
                        <p class="text-xs text-slate-500">Flux des réparations récentes</p>
                    </div>
                    <a href="{{ route('tickets.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">
                        Voir tout &rarr;
                    </a>
                </div>

                @if($recentTickets->isEmpty())
                <div class="p-10 text-center text-slate-400">
                    <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <p class="font-medium text-slate-600 text-sm">Aucun ticket de réparation pour l'instant.</p>
                    <p class="text-xs text-slate-400 mt-1">Commencez par enregistrer un premier appareil déposé.</p>
                    <a href="{{ route('tickets.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-xl text-xs font-semibold shadow-sm hover:bg-blue-700 transition">
                        Créer un ticket
                    </a>
                </div>
                @else
                <div class="divide-y divide-slate-100 overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-2.5">Réf & Date</th>
                                <th class="px-4 py-2.5">Client & Appareil</th>
                                <th class="px-4 py-2.5">Problème</th>
                                <th class="px-4 py-2.5">Statut</th>
                                <th class="px-4 py-2.5 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($recentTickets as $ticket)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-5 py-3 font-semibold text-slate-900 whitespace-nowrap">
                                    <a href="{{ route('tickets.show', $ticket) }}" class="text-blue-600 hover:underline">
                                        {{ $ticket->reference }}
                                    </a>
                                    <div class="text-[11px] text-slate-400 font-normal">{{ $ticket->deposit_date->format('d/m/Y H:i') }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-800">{{ $ticket->customer->name }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $ticket->device->brand }} {{ $ticket->device->model }}</div>
                                </td>
                                <td class="px-4 py-3 max-w-xs truncate text-slate-600">
                                    {{ $ticket->reported_issue }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium border {{ $ticket->status->badgeClasses() }}">
                                        {{ $ticket->status->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('tickets.show', $ticket) }}" class="inline-flex items-center px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium transition text-[11px]">
                                        Détails
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            <!-- Fil d'Activité Récente (1 colonne) -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5">
                <div class="border-b border-slate-200 pb-3 mb-4">
                    <h2 class="text-base font-bold text-slate-900">Activité Récente</h2>
                    <p class="text-xs text-slate-500">Changements de statut récents</p>
                </div>

                @if($recentHistories->isEmpty())
                <div class="p-6 text-center text-slate-400 text-xs">
                    Aucune transition de statut récente enregistrée.
                </div>
                @else
                <ol class="relative border-l border-slate-200 ml-3 space-y-4">
                    @foreach($recentHistories as $history)
                    <li class="mb-4 ml-4">
                        <div class="absolute w-3 h-3 bg-blue-600 rounded-full mt-1.5 -left-1.5 border border-white"></div>
                        <time class="mb-1 text-[10px] font-normal leading-none text-slate-400">{{ $history->created_at->diffForHumans() }}</time>
                        <div class="text-xs font-semibold text-slate-800">
                            {{ $history->ticket->reference }} &rarr; <span class="text-blue-600">{{ $history->to_status->label() }}</span>
                        </div>
                        <div class="text-[11px] text-slate-500">
                            {{ $history->ticket->device->brand ?? '' }} {{ $history->ticket->device->model ?? '' }}
                            @if($history->user)
                            <span class="text-slate-400">par {{ $history->user->name }}</span>
                            @endif
                        </div>
                        @if($history->reason)
                        <div class="text-[11px] italic text-slate-600 mt-0.5 bg-slate-50 p-1 rounded">
                            « {{ $history->reason }} »
                        </div>
                        @endif
                    </li>
                    @endforeach
                </ol>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
