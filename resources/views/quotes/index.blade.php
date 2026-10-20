<x-layouts.app>
    <x-slot:title>Devis</x-slot:title>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Devis de Réparation</h1>
                <p class="text-sm text-slate-500 mt-0.5">Propositions financières et estimations soumises aux clients</p>
            </div>
            <div>
                <a href="{{ route('quotes.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-xl shadow-sm transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nouveau Devis
                </a>
            </div>
        </div>

        <!-- Filtres et Recherche -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex flex-col sm:flex-row gap-3 items-center justify-between">
            <form action="{{ route('quotes.index') }}" method="GET" class="w-full flex flex-col sm:flex-row gap-2">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Réf devis, client, téléphone..."
                           class="w-full pl-9 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                </div>

                <div class="w-full sm:w-48">
                    <select name="status" onchange="this.form.submit()" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        <option value="">Tous les statuts</option>
                        @foreach(\App\Enums\QuoteStatus::cases() as $st)
                        <option value="{{ $st->value }}" {{ request('status') === $st->value ? 'selected' : '' }}>
                            {{ $st->label() }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </form>
            @if(request('search') || request('status'))
            <a href="{{ route('quotes.index') }}" class="text-xs text-slate-500 hover:text-slate-800 shrink-0">Réinitialiser</a>
            @endif
        </div>

        <!-- Table des Devis -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            @if($quotes->isEmpty())
            <div class="p-10 text-center text-slate-400">
                <svg class="w-12 h-12 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <p class="font-medium text-slate-700 text-sm">Aucun devis trouvé</p>
                <p class="text-xs text-slate-400 mt-0.5">Créez votre premier devis depuis un ticket de réparation.</p>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-xs font-semibold uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3">Réf Devis</th>
                            <th class="px-6 py-3">Ticket Associé</th>
                            <th class="px-6 py-3">Client</th>
                            <th class="px-6 py-3 text-right">Montant Total</th>
                            <th class="px-6 py-3 text-center">Statut</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($quotes as $quote)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-4 font-mono font-bold text-slate-900 whitespace-nowrap">
                                <a href="{{ route('quotes.show', $quote) }}" class="text-blue-600 hover:underline">
                                    {{ $quote->reference }}
                                </a>
                                <div class="text-[11px] text-slate-400 font-sans font-normal">{{ $quote->created_at->format('d/m/Y') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('tickets.show', $quote->ticket) }}" class="text-slate-800 font-medium hover:underline">
                                    {{ $quote->ticket->reference }}
                                </a>
                                <div class="text-[11px] text-slate-400">{{ $quote->ticket->device->brand ?? '' }} {{ $quote->ticket->device->model ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('customers.show', $quote->customer) }}" class="text-slate-900 font-semibold hover:underline">
                                    {{ $quote->customer->name }}
                                </a>
                                <div class="text-[11px] text-slate-400">{{ $quote->customer->phone }}</div>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap font-bold text-slate-900">
                                {{ number_format($quote->total_amount, 0, ',', ' ') }} {{ $quote->currency }}
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $quote->status->badgeClasses() }}">
                                    {{ $quote->status->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap space-x-1.5">
                                <a href="{{ route('quotes.show', $quote) }}" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-medium transition">
                                    Consulter
                                </a>
                                <a href="{{ route('quotes.print', $quote) }}" target="_blank" class="px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded text-xs font-medium transition">
                                    Imprimer
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-slate-200">
                {{ $quotes->links() }}
            </div>
            @endif
        </div>
    </div>
</x-layouts.app>
