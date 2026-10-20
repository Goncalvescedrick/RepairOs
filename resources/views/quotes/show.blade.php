<x-layouts.app>
    <x-slot:title>Devis {{ $quote->reference }}</x-slot:title>

    <div class="space-y-6" x-data="{ refuseModalOpen: false }">
        <!-- Header -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center space-x-3">
                    <span class="font-mono text-2xl font-extrabold text-slate-900 tracking-tight">{{ $quote->reference }}</span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {{ $quote->status->badgeClasses() }}">
                        {{ $quote->status->label() }}
                    </span>
                </div>
                <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 mt-1.5">
                    <span>Créé le <strong>{{ $quote->created_at->format('d/m/Y') }}</strong></span>
                    @if($quote->valid_until)
                    <span>&bull;</span>
                    <span>Valide jusqu'au <strong>{{ $quote->valid_until->format('d/m/Y') }}</strong></span>
                    @endif
                    <span>&bull;</span>
                    <span>Ticket : <a href="{{ route('tickets.show', $quote->ticket) }}" class="text-blue-600 font-mono font-semibold hover:underline">{{ $quote->ticket->reference }}</a></span>
                </div>
            </div>

            <!-- Actions Bar -->
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ $whatsAppUrl }}" target="_blank" class="inline-flex items-center px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-600 hover:text-white text-xs font-semibold transition" title="Envoyer le devis par WhatsApp">
                    <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                    WhatsApp Devis
                </a>

                <a href="{{ route('quotes.print', $quote) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs font-semibold transition">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Imprimer / PDF
                </a>

                @if($quote->status === \App\Enums\QuoteStatus::BROUILLON)
                <form action="{{ route('quotes.send', $quote) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition shadow-xs">
                        Marquer comme Envoyé
                    </button>
                </form>
                @endif

                @if(in_array($quote->status, [\App\Enums\QuoteStatus::BROUILLON, \App\Enums\QuoteStatus::ENVOYE]))
                <form action="{{ route('quotes.accept', $quote) }}" method="POST" class="inline" onsubmit="return confirm('Confirmez-vous l’accord du client sur ce devis ? Cela passera la réparation en cours.');">
                    @csrf
                    <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition shadow-xs">
                        Valider Accord Client
                    </button>
                </form>

                <button type="button" @click="refuseModalOpen = true" class="px-3.5 py-1.5 rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-semibold transition border border-rose-200">
                    Refus Client...
                </button>
                @endif

                @if($quote->status === \App\Enums\QuoteStatus::ACCEPTE)
                <a href="{{ route('invoices.create', ['quote_id' => $quote->id]) }}" class="px-4 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold transition shadow-xs">
                    + Émettre la Facture
                </a>
                @endif
            </div>
        </div>

        <!-- Détail Devis -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden p-6 sm:p-8 space-y-6">
            <!-- Client & Équipement -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pb-6 border-b border-slate-200 text-xs">
                <div>
                    <span class="text-slate-400 font-semibold uppercase tracking-wider block mb-1">Destinataire (Client)</span>
                    <div class="text-sm font-bold text-slate-900">{{ $quote->customer->name }}</div>
                    <div class="text-slate-600 mt-0.5">Tél : {{ $quote->customer->phone }}</div>
                    @if($quote->customer->address)
                    <div class="text-slate-500">{{ $quote->customer->address }}</div>
                    @endif
                </div>

                <div>
                    <span class="text-slate-400 font-semibold uppercase tracking-wider block mb-1">Équipement Pris en Charge</span>
                    <div class="text-sm font-bold text-slate-900">{{ $quote->ticket->device->brand }} {{ $quote->ticket->device->model }}</div>
                    <div class="text-slate-500 mt-0.5">Catégorie : {{ $quote->ticket->device->category_label }}</div>
                    <div class="text-slate-500 font-mono">S/N : {{ $quote->ticket->device->serial_number ?: 'N/A' }}</div>
                </div>
            </div>

            <!-- Tableau des Prestations & Pièces -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3 w-28">Type</th>
                            <th class="px-4 py-3">Désignation</th>
                            <th class="px-4 py-3 text-center w-24">Quantité</th>
                            <th class="px-4 py-3 text-right w-36">Prix Unitaire</th>
                            <th class="px-4 py-3 text-right w-36">Total Ligne</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($quote->items as $item)
                        <tr>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $item->type === 'SERVICE' ? 'bg-blue-50 text-blue-700' : 'bg-emerald-50 text-emerald-700' }}">
                                    {{ $item->type === 'SERVICE' ? 'Prestation' : 'Pièce' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-medium text-slate-900">
                                {{ $item->description }}
                            </td>
                            <td class="px-4 py-3 text-center font-medium text-slate-700">
                                {{ number_format($item->quantity, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-slate-700">
                                {{ number_format($item->unit_price, 0, ',', ' ') }} {{ $quote->currency }}
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-slate-900">
                                {{ number_format($item->total_price, 0, ',', ' ') }} {{ $quote->currency }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Totaux -->
            <div class="flex justify-end pt-4 border-t border-slate-200">
                <div class="w-full sm:w-80 space-y-2 text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>Sous-total HT :</span>
                        <span class="font-bold text-slate-900">{{ number_format($quote->subtotal_amount, 0, ',', ' ') }} {{ $quote->currency }}</span>
                    </div>

                    @if((float) $quote->discount_amount > 0)
                    <div class="flex justify-between text-emerald-700">
                        <span>Remise accordée :</span>
                        <span>- {{ number_format($quote->discount_amount, 0, ',', ' ') }} {{ $quote->currency }}</span>
                    </div>
                    @endif

                    @if((float) $quote->tax_rate > 0)
                    <div class="flex justify-between text-slate-600">
                        <span>TVA ({{ $quote->tax_rate }}%) :</span>
                        <span>{{ number_format($quote->tax_amount, 0, ',', ' ') }} {{ $quote->currency }}</span>
                    </div>
                    @endif

                    <div class="pt-2 border-t border-slate-200 flex justify-between text-base font-extrabold text-slate-900">
                        <span>Total TTC :</span>
                        <span class="text-blue-700 text-lg">{{ number_format($quote->total_amount, 0, ',', ' ') }} {{ $quote->currency }}</span>
                    </div>
                </div>
            </div>

            @if($quote->notes)
            <div class="pt-4 border-t border-slate-200 text-xs">
                <span class="font-bold text-slate-800 uppercase tracking-wider block mb-1">Conditions & Remarques :</span>
                <p class="text-slate-600 whitespace-pre-line bg-slate-50 p-3 rounded-xl border border-slate-100">{{ $quote->notes }}</p>
            </div>
            @endif
        </div>

        <!-- Modale de Refus Devis -->
        <div x-show="refuseModalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200 space-y-4">
                <div class="flex items-center space-x-2 text-rose-600 font-bold text-base">
                    <span>Enregistrer le Refus du Devis</span>
                </div>
                <p class="text-xs text-slate-600">
                    Indiquez le motif invoqué par le client (coût trop élevé, abandon de l'appareil, délai...).
                </p>

                <form action="{{ route('quotes.refuse', $quote) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="refusal_reason" class="block text-xs font-semibold text-slate-700 mb-1">Motif du refus (facultatif)</label>
                        <textarea id="refusal_reason" name="refusal_reason" rows="3" placeholder="ex: Prix des pièces jugé trop cher par le client..." class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg"></textarea>
                    </div>

                    <div class="flex items-center justify-end space-x-2 pt-2 border-t border-slate-100">
                        <button type="button" @click="refuseModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800">
                            Annuler
                        </button>
                        <button type="submit" class="px-4 py-2 text-xs font-semibold bg-rose-600 hover:bg-rose-700 text-white rounded-lg transition">
                            Confirmer le Refus
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
