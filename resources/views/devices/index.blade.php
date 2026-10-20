<x-layouts.app>
    <x-slot:title>Parc d'Appareils</x-slot:title>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Appareils Déposés</h1>
                <p class="text-sm text-slate-500 mt-0.5">Équipements informatiques et électroniques pris en charge</p>
            </div>
            <div>
                <a href="{{ route('devices.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-xl shadow-sm transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Enregistrer un Appareil
                </a>
            </div>
        </div>

        <!-- Filtres et Recherche -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex flex-col md:flex-row gap-3 items-center justify-between">
            <form action="{{ route('devices.index') }}" method="GET" class="w-full flex flex-col sm:flex-row gap-2">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Recherche par marque, modèle, n° de série, nom client..."
                           class="w-full pl-9 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                </div>

                <div class="w-full sm:w-48">
                    <select name="category" onchange="this.form.submit()" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        <option value="">Toutes catégories</option>
                        <option value="laptop" {{ request('category') === 'laptop' ? 'selected' : '' }}>Ordinateur portable</option>
                        <option value="desktop" {{ request('category') === 'desktop' ? 'selected' : '' }}>Ordinateur fixe</option>
                        <option value="printer" {{ request('category') === 'printer' ? 'selected' : '' }}>Imprimante</option>
                        <option value="network" {{ request('category') === 'network' ? 'selected' : '' }}>Réseau</option>
                        <option value="other" {{ request('category') === 'other' ? 'selected' : '' }}>Autre</option>
                    </select>
                </div>
            </form>
            @if(request('search') || request('category'))
            <a href="{{ route('devices.index') }}" class="text-xs text-slate-500 hover:text-slate-800 shrink-0">Réinitialiser</a>
            @endif
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            @if($devices->isEmpty())
            <div class="p-10 text-center text-slate-400">
                <svg class="w-12 h-12 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <p class="font-medium text-slate-700 text-sm">Aucun appareil trouvé</p>
                <p class="text-xs text-slate-400 mt-0.5">Enregistrez un premier appareil pour ce client.</p>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-xs font-semibold uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3">Équipement (Marque & Modèle)</th>
                            <th class="px-6 py-3">Catégorie</th>
                            <th class="px-6 py-3">N° de Série</th>
                            <th class="px-6 py-3">Client Propriétaire</th>
                            <th class="px-6 py-3 text-center">Historique</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($devices as $device)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-4 font-semibold text-slate-900 whitespace-nowrap">
                                <a href="{{ route('devices.show', $device) }}" class="text-blue-600 hover:underline">
                                    {{ $device->brand }} {{ $device->model }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700">
                                    {{ $device->category_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-600 text-xs whitespace-nowrap font-mono">
                                {{ $device->serial_number ?: 'Non spécifié' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('customers.show', $device->customer) }}" class="font-medium text-slate-800 hover:underline">
                                    {{ $device->customer->name }}
                                </a>
                                <div class="text-[11px] text-slate-400">{{ $device->customer->phone }}</div>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                    {{ $device->repair_tickets_count }} ticket(s)
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap space-x-2">
                                <a href="{{ route('devices.show', $device) }}" class="text-xs font-medium text-slate-600 hover:text-slate-900 px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 transition">
                                    Consulter
                                </a>
                                <a href="{{ route('tickets.create', ['customer_id' => $device->customer_id, 'device_id' => $device->id]) }}" class="text-xs font-medium text-blue-600 hover:text-blue-800 px-2 py-1 rounded bg-blue-50 hover:bg-blue-100 transition">
                                    + Nouveau Ticket
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-slate-200">
                {{ $devices->links() }}
            </div>
            @endif
        </div>
    </div>
</x-layouts.app>
