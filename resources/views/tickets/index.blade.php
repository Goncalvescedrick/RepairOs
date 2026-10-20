<x-layouts.app>
    <x-slot:title>Tickets de Réparation</x-slot:title>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Tickets de Réparation</h1>
                <p class="text-sm text-slate-500 mt-0.5">Suivi opérationnel des interventions et diagnostics en atelier</p>
            </div>
            <div>
                <a href="{{ route('tickets.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-xl shadow-sm transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nouveau Ticket
                </a>
            </div>
        </div>

        <!-- Filtres et Recherche -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs space-y-3">
            <form action="{{ route('tickets.index') }}" method="GET" class="flex flex-col md:flex-row gap-2">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Réf, client, téléphone, marque, n° série..."
                           class="w-full pl-9 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                </div>

                <div class="w-full sm:w-48">
                    <select name="status" onchange="this.form.submit()" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        <option value="">Tous les statuts</option>
                        @foreach(\App\Enums\RepairStatus::cases() as $st)
                        <option value="{{ $st->value }}" {{ request('status') === $st->value ? 'selected' : '' }}>
                            {{ $st->label() }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full sm:w-40">
                    <select name="priority" onchange="this.form.submit()" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        <option value="">Toutes priorités</option>
                        @foreach(\App\Enums\TicketPriority::cases() as $pr)
                        <option value="{{ $pr->value }}" {{ request('priority') === $pr->value ? 'selected' : '' }}>
                            {{ $pr->label() }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full sm:w-44">
                    <select name="assigned_to" onchange="this.form.submit()" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        <option value="">Tous techniciens</option>
                        @foreach($technicians as $tech)
                        <option value="{{ $tech->id }}" {{ request('assigned_to') == $tech->id ? 'selected' : '' }}>
                            {{ $tech->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-semibold hover:bg-slate-900 transition">
                    Filtrer
                </button>
            </form>

            <div class="flex items-center space-x-4 text-xs pt-1 border-t border-slate-100">
                <a href="{{ route('tickets.index', ['overdue' => 1]) }}" class="inline-flex items-center font-medium {{ request('overdue') ? 'text-rose-700 font-bold' : 'text-slate-600 hover:text-slate-900' }}">
                    <span class="w-2 h-2 rounded-full bg-rose-500 mr-1.5"></span>
                    Tickets en retard uniquement
                </a>
                @if(request()->hasAny(['search', 'status', 'priority', 'assigned_to', 'overdue']))
                <a href="{{ route('tickets.index') }}" class="text-blue-600 hover:underline">
                    Effacer tous les filtres
                </a>
                @endif
            </div>
        </div>

        <!-- Table des Tickets -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            @if($tickets->isEmpty())
            <div class="p-10 text-center text-slate-400">
                <svg class="w-12 h-12 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                <p class="font-medium text-slate-700 text-sm">Aucun ticket trouvé</p>
                <p class="text-xs text-slate-400 mt-0.5">Aucune réparation ne correspond aux critères sélectionnés.</p>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-xs font-semibold uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-5 py-3">Réf Ticket</th>
                            <th class="px-5 py-3">Client</th>
                            <th class="px-5 py-3">Appareil</th>
                            <th class="px-5 py-3">Panne déclarée</th>
                            <th class="px-5 py-3">Technicien</th>
                            <th class="px-5 py-3">Priorité</th>
                            <th class="px-5 py-3">Statut</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($tickets as $ticket)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-4 whitespace-nowrap font-mono font-bold text-slate-900">
                                <a href="{{ route('tickets.show', $ticket) }}" class="text-blue-600 hover:underline">
                                    {{ $ticket->reference }}
                                </a>
                                <div class="text-[11px] text-slate-400 font-normal font-sans">{{ $ticket->deposit_date->format('d/m/Y') }}</div>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <a href="{{ route('customers.show', $ticket->customer) }}" class="font-semibold text-slate-800 hover:underline">
                                    {{ $ticket->customer->name }}
                                </a>
                                <div class="text-[11px] text-slate-400">{{ $ticket->customer->phone }}</div>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <a href="{{ route('devices.show', $ticket->device) }}" class="text-slate-800 hover:underline">
                                    {{ $ticket->device->brand }} {{ $ticket->device->model }}
                                </a>
                                <div class="text-[11px] text-slate-400">{{ $ticket->device->category_label }}</div>
                            </td>
                            <td class="px-5 py-4 max-w-xs truncate text-slate-600 text-xs">
                                {{ $ticket->reported_issue }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-xs text-slate-600">
                                {{ $ticket->assignedTo?->name ?: 'Non assigné' }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border {{ $ticket->priority->badgeClasses() }}">
                                    {{ $ticket->priority->label() }}
                                </span>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border {{ $ticket->status->badgeClasses() }}">
                                    {{ $ticket->status->label() }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right whitespace-nowrap space-x-1.5">
                                <a href="{{ route('tickets.show', $ticket) }}" class="text-xs font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 px-2.5 py-1 rounded transition">
                                    Détails
                                </a>
                                <a href="{{ route('tickets.receipt', $ticket) }}" target="_blank" class="text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 px-2.5 py-1 rounded transition" title="Imprimer le bon de dépôt">
                                    Reçu
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-slate-200">
                {{ $tickets->links() }}
            </div>
            @endif
        </div>
    </div>
</x-layouts.app>
