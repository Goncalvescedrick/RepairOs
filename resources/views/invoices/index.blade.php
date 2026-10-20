<x-layouts.app>
    <x-slot:title>Factures & Paiements</x-slot:title>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Factures & Encaissements</h1>
                <p class="text-sm text-slate-500 mt-0.5">Documents comptables, règlements partiels et soldes clients</p>
            </div>
            <div>
                <a href="{{ route('invoices.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-xl shadow-sm transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Émettre une Facture
                </a>
            </div>
        </div>

        <!-- Filtres et Recherche -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex flex-col sm:flex-row gap-3 items-center justify-between">
            <form action="{{ route('invoices.index') }}" method="GET" class="w-full flex flex-col sm:flex-row gap-2">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Réf facture, client, téléphone..."
                           class="w-full pl-9 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                </div>

                <div class="w-full sm:w-48">
                    <select name="status" onchange="this.form.submit()" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        <option value="">Tous les statuts</option>
                        @foreach(\App\Enums\InvoiceStatus::cases() as $st)
                        <option value="{{ $st->value }}" {{ request('status') === $st->value ? 'selected' : '' }}>
                            {{ $st->label() }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </form>
            @if(request('search') || request('status'))
            <a href="{{ route('invoices.index') }}" class="text-xs text-slate-500 hover:text-slate-800 shrink-0">Réinitialiser</a>
            @endif
        </div>

        <!-- Table des Factures -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            @if($invoices->isEmpty())
            <div class="p-10 text-center text-slate-400">
                <svg class="w-12 h-12 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="font-medium text-slate-700 text-sm">Aucune facture enregistrée</p>
                <p class="text-xs text-slate-400 mt-0.5">Émettez une facture pour les réparations achevées.</p>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-xs font-semibold uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3">Réf Facture</th>
                            <th class="px-6 py-3">Ticket / Client</th>
                            <th class="px-6 py-3 text-right">Total Facturé</th>
                            <th class="px-6 py-3 text-right">Montant Encaissé</th>
                            <th class="px-6 py-3 text-right">Solde Dû</th>
                            <th class="px-6 py-3 text-center">Statut</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($invoices as $invoice)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-4 font-mono font-bold text-slate-900 whitespace-nowrap">
                                <a href="{{ route('invoices.show', $invoice) }}" class="text-blue-600 hover:underline">
                                    {{ $invoice->reference }}
                                </a>
                                <div class="text-[11px] text-slate-400 font-sans font-normal">{{ $invoice->issued_at->format('d/m/Y') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('customers.show', $invoice->customer) }}" class="font-semibold text-slate-900 hover:underline">
                                    {{ $invoice->customer->name }}
                                </a>
                                <div class="text-[11px] text-slate-500 font-mono">
                                    Ticket : {{ $invoice->ticket->reference }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap font-bold text-slate-900">
                                {{ number_format($invoice->total_amount, 0, ',', ' ') }} {{ $invoice->currency }}
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap font-semibold text-emerald-700">
                                {{ number_format($invoice->paid_amount, 0, ',', ' ') }} {{ $invoice->currency }}
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap font-bold {{ (float)$invoice->balance_due > 0 ? 'text-amber-700' : 'text-slate-400' }}">
                                {{ number_format($invoice->balance_due, 0, ',', ' ') }} {{ $invoice->currency }}
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $invoice->status->badgeClasses() }}">
                                    {{ $invoice->status->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap space-x-1.5">
                                <a href="{{ route('invoices.show', $invoice) }}" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-medium transition">
                                    Détails & Règlements
                                </a>
                                <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded text-xs font-medium transition">
                                    Imprimer
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-slate-200">
                {{ $invoices->links() }}
            </div>
            @endif
        </div>
    </div>
</x-layouts.app>
