<x-layouts.app>
    <x-slot:title>Clients</x-slot:title>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Fichier Clients</h1>
                <p class="text-sm text-slate-500 mt-0.5">Gestion du répertoire et historique des clients de l'atelier</p>
            </div>
            <div>
                <a href="{{ route('customers.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-xl shadow-sm transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    Nouveau Client
                </a>
            </div>
        </div>

        <!-- Recherche et Filtres -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center justify-between">
            <form action="{{ route('customers.index') }}" method="GET" class="w-full max-w-md relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par nom, téléphone, email..."
                       class="w-full pl-9 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
            </form>
            @if(request('search'))
            <a href="{{ route('customers.index') }}" class="text-xs text-slate-500 hover:text-slate-800 ml-3">Réinitialiser</a>
            @endif
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            @if($customers->isEmpty())
            <div class="p-10 text-center text-slate-400">
                <svg class="w-12 h-12 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <p class="font-medium text-slate-700 text-sm">Aucun client trouvé</p>
                <p class="text-xs text-slate-400 mt-0.5">Créez votre première fiche client pour démarrer.</p>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-xs font-semibold uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3">Nom du Client</th>
                            <th class="px-6 py-3">Téléphone</th>
                            <th class="px-6 py-3">E-mail / Ville</th>
                            <th class="px-6 py-3 text-center">Appareils</th>
                            <th class="px-6 py-3 text-center">Tickets</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($customers as $customer)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-4 font-semibold text-slate-900 whitespace-nowrap">
                                <a href="{{ route('customers.show', $customer) }}" class="text-blue-600 hover:underline">
                                    {{ $customer->name }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-slate-700 whitespace-nowrap font-medium">
                                {{ $customer->phone }}
                            </td>
                            <td class="px-6 py-4 text-slate-500 text-xs truncate max-w-xs">
                                <div>{{ $customer->email ?: '—' }}</div>
                                @if($customer->address)
                                <div class="text-[11px] text-slate-400">{{ Str::limit($customer->address, 30) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                    {{ $customer->devices_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                    {{ $customer->repair_tickets_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap space-x-2">
                                <a href="{{ route('customers.show', $customer) }}" class="text-xs font-medium text-slate-600 hover:text-slate-900 px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 transition">
                                    Consulter
                                </a>
                                <a href="{{ route('customers.edit', $customer) }}" class="text-xs font-medium text-blue-600 hover:text-blue-800 px-2 py-1 rounded bg-blue-50 hover:bg-blue-100 transition">
                                    Modifier
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-slate-200">
                {{ $customers->links() }}
            </div>
            @endif
        </div>
    </div>
</x-layouts.app>
