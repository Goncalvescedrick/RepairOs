<x-layouts.app>
    <x-slot:title>Fiche Client — {{ $customer->name }}</x-slot:title>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xl">
                    {{ substr($customer->name, 0, 2) }}
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">{{ $customer->name }}</h1>
                    <div class="flex items-center space-x-2 text-xs text-slate-500 mt-0.5">
                        <span>Client depuis le {{ $customer->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('customers.edit', $customer) }}" class="px-3.5 py-1.5 rounded-xl border border-slate-300 text-slate-700 font-medium text-xs hover:bg-slate-100 transition">
                    Modifier la fiche
                </a>
                <a href="{{ route('tickets.create', ['customer_id' => $customer->id]) }}" class="px-3.5 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-sm transition">
                    + Nouveau Ticket pour ce client
                </a>
            </div>
        </div>

        <!-- 3 Cartes Récapitulatives -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Coordonnées -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-2.5">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Contact Direct</div>
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-900 text-base">{{ $customer->phone }}</span>
                    @php
                        $cleanPhone = preg_replace('/[^0-9]/', '', $customer->phone);
                        if (strlen($cleanPhone) === 8) $cleanPhone = '226' . $cleanPhone;
                    @endphp
                    <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="p-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white rounded-lg transition" title="Ouvrir WhatsApp">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                    </a>
                </div>
                <div class="text-xs text-slate-500">{{ $customer->email ?: 'Pas d’e-mail renseigné' }}</div>
                <div class="text-xs text-slate-400">{{ $customer->address ?: 'Pas d’adresse précisée' }}</div>
            </div>

            <!-- Total Facturé -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Facturé</div>
                <div class="text-2xl font-extrabold text-slate-900 mt-2">
                    {{ number_format($totalInvoiced, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-500">FCFA</span>
                </div>
                <div class="text-xs text-slate-400 mt-1">Cumul des factures émises</div>
            </div>

            <!-- Total Encaissé -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Réglé</div>
                <div class="text-2xl font-extrabold text-emerald-700 mt-2">
                    {{ number_format($totalPaid, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-500">FCFA</span>
                </div>
                <div class="text-xs text-slate-400 mt-1">Montant encaissé réel</div>
            </div>

            <!-- Solde restant dû -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Solde Dû</div>
                <div class="text-2xl font-extrabold text-amber-700 mt-2">
                    {{ number_format($totalDue, 0, ',', ' ') }} <span class="text-xs font-normal text-slate-500">FCFA</span>
                </div>
                <div class="text-xs text-slate-400 mt-1">Créance restant à percevoir</div>
            </div>
        </div>

        <!-- Deux sections : Appareils & Historique des Réparations -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Appareils du client (1 col) -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200 mb-4">
                    <h2 class="text-base font-bold text-slate-900">Appareils enregistrés ({{ $customer->devices->count() }})</h2>
                    <a href="{{ route('devices.create', ['customer_id' => $customer->id]) }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800">
                        + Ajouter
                    </a>
                </div>

                @if($customer->devices->isEmpty())
                <div class="p-6 text-center text-slate-400 text-xs">
                    Aucun appareil enregistré pour ce client.
                </div>
                @else
                <div class="space-y-3">
                    @foreach($customer->devices as $device)
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-800 text-sm">{{ $device->brand }} {{ $device->model }}</span>
                            <span class="text-[10px] font-medium uppercase px-2 py-0.5 bg-slate-200 text-slate-700 rounded">
                                {{ $device->category_label }}
                            </span>
                        </div>
                        @if($device->serial_number)
                        <div class="text-xs text-slate-500 mt-1">S/N : {{ $device->serial_number }}</div>
                        @endif
                        <div class="mt-2 pt-2 border-t border-slate-200 flex justify-end">
                            <a href="{{ route('devices.show', $device) }}" class="text-[11px] font-semibold text-blue-600 hover:underline">
                                Voir fiche appareil &rarr;
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Historique des Tickets (2 cols) -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Historique des Réparations</h2>
                        <p class="text-xs text-slate-500">Toutes les interventions de ce client</p>
                    </div>
                </div>

                @if($customer->repairTickets->isEmpty())
                <div class="p-10 text-center text-slate-400">
                    <p class="text-sm font-medium text-slate-600">Aucun ticket pour ce client pour le moment.</p>
                </div>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-2.5">Réf & Date</th>
                                <th class="px-4 py-2.5">Appareil</th>
                                <th class="px-4 py-2.5">Panne déclarée</th>
                                <th class="px-4 py-2.5">Statut</th>
                                <th class="px-4 py-2.5 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($customer->repairTickets as $ticket)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-5 py-3 font-semibold text-slate-900 whitespace-nowrap">
                                    <a href="{{ route('tickets.show', $ticket) }}" class="text-blue-600 hover:underline">
                                        {{ $ticket->reference }}
                                    </a>
                                    <div class="text-[11px] text-slate-400 font-normal">{{ $ticket->deposit_date->format('d/m/Y') }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="font-medium text-slate-800">{{ $ticket->device->brand }} {{ $ticket->device->model }}</div>
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
                                    <a href="{{ route('tickets.show', $ticket) }}" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 rounded text-slate-700 font-medium transition text-[11px]">
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
        </div>
    </div>
</x-layouts.app>
