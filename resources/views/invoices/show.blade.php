<x-layouts.app>
    <x-slot:title>Facture {{ $invoice->reference }}</x-slot:title>

    <div class="space-y-6" x-data="{ cancelInvoiceModal: false, cancelPaymentModal: false, cancelPaymentId: null }">
        <!-- Header -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center space-x-3">
                    <span class="font-mono text-2xl font-extrabold text-slate-900 tracking-tight">{{ $invoice->reference }}</span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {{ $invoice->status->badgeClasses() }}">
                        {{ $invoice->status->label() }}
                    </span>
                </div>
                <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 mt-1.5">
                    <span>Émise le <strong>{{ $invoice->issued_at->format('d/m/Y') }}</strong></span>
                    @if($invoice->due_date)
                    <span>&bull;</span>
                    <span>Échéance : <strong>{{ $invoice->due_date->format('d/m/Y') }}</strong></span>
                    @endif
                    <span>&bull;</span>
                    <span>Ticket : <a href="{{ route('tickets.show', $invoice->ticket) }}" class="text-blue-600 font-mono font-semibold hover:underline">{{ $invoice->ticket->reference }}</a></span>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="inline-flex items-center px-3.5 py-1.5 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs font-semibold transition">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Imprimer Facture
                </a>

                @if((float) $invoice->paid_amount == 0 && $invoice->status !== \App\Enums\InvoiceStatus::ANNULEE)
                <button type="button" @click="cancelInvoiceModal = true" class="px-3.5 py-1.5 rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-semibold transition border border-rose-200">
                    Annuler la Facture...
                </button>
                @endif
            </div>
        </div>

        <!-- 3 Blocs : Détails Facture, Lignes, et Section Règlements -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Colonne Gauche : Lignes & Totaux (2 cols) -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 sm:p-8 space-y-6">
                    <!-- Client & Ticket -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pb-6 border-b border-slate-200 text-xs">
                        <div>
                            <span class="text-slate-400 font-semibold uppercase tracking-wider block mb-1">Facturé à</span>
                            <div class="text-sm font-bold text-slate-900">{{ $invoice->customer->name }}</div>
                            <div class="text-slate-600 mt-0.5">Tél : {{ $invoice->customer->phone }}</div>
                            <div class="text-slate-500">{{ $invoice->customer->address ?: 'Adresse non renseignée' }}</div>
                        </div>

                        <div>
                            <span class="text-slate-400 font-semibold uppercase tracking-wider block mb-1">Objet de la réparation</span>
                            <div class="text-sm font-bold text-slate-900">{{ $invoice->ticket->device->brand }} {{ $invoice->ticket->device->model }}</div>
                            <div class="text-slate-500 font-mono">Ticket réf : {{ $invoice->ticket->reference }}</div>
                        </div>
                    </div>

                    <!-- Tableau des Lignes Facturées -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3 w-28">Type</th>
                                    <th class="px-4 py-3">Description</th>
                                    <th class="px-4 py-3 text-center w-24">Quantité</th>
                                    <th class="px-4 py-3 text-right w-36">Prix Unit.</th>
                                    <th class="px-4 py-3 text-right w-36">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($invoice->items as $item)
                                <tr>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $item->type === 'SERVICE' ? 'bg-blue-50 text-blue-700' : 'bg-emerald-50 text-emerald-700' }}">
                                            {{ $item->type === 'SERVICE' ? 'Prestation' : 'Pièce' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-slate-900">{{ $item->description }}</td>
                                    <td class="px-4 py-3 text-center text-slate-700">{{ number_format($item->quantity, 2) }}</td>
                                    <td class="px-4 py-3 text-right text-slate-700">{{ number_format($item->unit_price, 0, ',', ' ') }} {{ $invoice->currency }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-slate-900">{{ number_format($item->total_price, 0, ',', ' ') }} {{ $invoice->currency }}</td>
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
                                <span class="font-bold text-slate-900">{{ number_format($invoice->subtotal_amount, 0, ',', ' ') }} {{ $invoice->currency }}</span>
                            </div>

                            @if((float) $invoice->discount_amount > 0)
                            <div class="flex justify-between text-emerald-700">
                                <span>Remise :</span>
                                <span>- {{ number_format($invoice->discount_amount, 0, ',', ' ') }} {{ $invoice->currency }}</span>
                            </div>
                            @endif

                            @if((float) $invoice->tax_rate > 0)
                            <div class="flex justify-between text-slate-600">
                                <span>TVA ({{ $invoice->tax_rate }}%) :</span>
                                <span>{{ number_format($invoice->tax_amount, 0, ',', ' ') }} {{ $invoice->currency }}</span>
                            </div>
                            @endif

                            <div class="pt-2 border-t border-slate-200 flex justify-between text-base font-extrabold text-slate-900">
                                <span>Total Net Facturé :</span>
                                <span class="text-blue-700 text-lg">{{ number_format($invoice->total_amount, 0, ',', ' ') }} {{ $invoice->currency }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Historique des Paiements Effectués -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
                    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">
                        Paiements et Règlements Enregistrés ({{ $invoice->payments->count() }})
                    </h2>

                    @if($invoice->payments->isEmpty())
                    <div class="p-6 text-center text-slate-400 text-xs">
                        Aucun règlement enregistré pour cette facture.
                    </div>
                    @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider">
                                <tr>
                                    <th class="px-4 py-2.5">Réf Reçu</th>
                                    <th class="px-4 py-2.5">Date & Heure</th>
                                    <th class="px-4 py-2.5">Moyen</th>
                                    <th class="px-4 py-2.5">Réf Transaction</th>
                                    <th class="px-4 py-2.5 text-right">Montant</th>
                                    <th class="px-4 py-2.5 text-right">Reçu</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($invoice->payments as $payment)
                                <tr>
                                    <td class="px-4 py-3 font-mono font-bold text-slate-900">{{ $payment->reference }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $payment->paid_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-800">
                                            {{ $payment->payment_method->label() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 font-mono text-slate-500">{{ $payment->transaction_reference ?: '—' }}</td>
                                    <td class="px-4 py-3 text-right font-extrabold text-emerald-700">
                                        {{ number_format($payment->amount, 0, ',', ' ') }} {{ $invoice->currency }}
                                    </td>
                                    <td class="px-4 py-3 text-right space-x-2">
                                        <a href="{{ route('payments.receipt', $payment) }}" target="_blank" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded font-medium text-slate-700 text-[11px]">
                                            Imprimer Reçu
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

            <!-- Colonne Droite : Soldes & Formulaire d'Encaissement (1 col) -->
            <div class="space-y-6">
                <!-- Synthèse de Soldes -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                    <div class="border-b border-slate-200 pb-2">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">État de Règlement</span>
                    </div>

                    <div class="space-y-3">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-500">Total facturé :</span>
                            <span class="font-bold text-slate-900">{{ number_format($invoice->total_amount, 0, ',', ' ') }} {{ $invoice->currency }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-500">Déjà réglé :</span>
                            <span class="font-extrabold text-emerald-700">{{ number_format($invoice->paid_amount, 0, ',', ' ') }} {{ $invoice->currency }}</span>
                        </div>
                        <div class="pt-2 border-t border-slate-100 flex justify-between items-center text-base">
                            <span class="font-bold text-slate-800">Reste à payer :</span>
                            <span class="font-extrabold {{ (float)$invoice->balance_due > 0 ? 'text-amber-700 text-lg' : 'text-emerald-700' }}">
                                {{ number_format($invoice->balance_due, 0, ',', ' ') }} {{ $invoice->currency }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Formulaire d'Encaissement -->
                @if((float) $invoice->balance_due > 0 && $invoice->status !== \App\Enums\InvoiceStatus::ANNULEE)
                <div id="reglement" class="bg-emerald-50/60 p-5 rounded-2xl border border-emerald-200 shadow-xs space-y-4">
                    <div class="border-b border-emerald-200 pb-2 flex items-center justify-between">
                        <span class="text-xs font-bold text-emerald-900 uppercase tracking-wider">Enregistrer un Règlement</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-200 text-emerald-900">Encaissement direct</span>
                    </div>

                    <form action="{{ route('invoices.payments.store', $invoice) }}" method="POST" class="space-y-3">
                        @csrf

                        <div>
                            <label for="amount" class="block text-xs font-semibold text-slate-700 mb-1">Montant versé ({{ $invoice->currency }}) *</label>
                            <input id="amount" type="number" step="100" min="1" max="{{ $invoice->balance_due }}" name="amount"
                                   value="{{ old('amount', $invoice->balance_due) }}" required
                                   class="w-full px-3 py-2 text-sm bg-white border border-slate-300 rounded-lg font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <p class="text-[11px] text-slate-500 mt-0.5">Maximum autorisé : {{ number_format($invoice->balance_due, 0, ',', ' ') }} {{ $invoice->currency }}</p>
                        </div>

                        <div>
                            <label for="payment_method" class="block text-xs font-semibold text-slate-700 mb-1">Moyen de règlement *</label>
                            <select id="payment_method" name="payment_method" required class="w-full px-3 py-2 text-sm bg-white border border-slate-300 rounded-lg">
                                <option value="ESPECES">Espèces (Cash)</option>
                                <option value="MOBILE_MONEY">Mobile Money (Orange / Moov / Wave)</option>
                                <option value="VIREMENT">Virement bancaire</option>
                                <option value="CARTE">Carte bancaire</option>
                                <option value="AUTRE">Autre</option>
                            </select>
                        </div>

                        <div>
                            <label for="transaction_reference" class="block text-xs font-semibold text-slate-700 mb-1">Réf Transaction / Reçu (Mobile Money)</label>
                            <input id="transaction_reference" type="text" name="transaction_reference" placeholder="ex: OM261020.1234.A0012"
                                   class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg">
                        </div>

                        <div>
                            <label for="paid_at" class="block text-xs font-semibold text-slate-700 mb-1">Date & Heure d'encaissement *</label>
                            <input id="paid_at" type="datetime-local" name="paid_at" value="{{ now()->format('Y-m-d\TH:i') }}" required
                                   class="w-full px-3 py-1.5 text-xs bg-white border border-slate-300 rounded-lg">
                        </div>

                        <button type="submit" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm rounded-xl shadow-sm transition">
                            Valider le paiement & Émettre reçu
                        </button>
                    </form>
                </div>
                @else
                <div class="bg-emerald-50 p-5 rounded-2xl border border-emerald-200 text-center text-xs text-emerald-800 font-semibold">
                    <svg class="w-8 h-8 mx-auto text-emerald-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Cette facture est entièrement soldée. Aucun solde restant dû.
                </div>
                @endif
            </div>
        </div>

        <!-- Modale d'Annulation Facture -->
        <div x-show="cancelInvoiceModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200 space-y-4">
                <div class="flex items-center space-x-2 text-rose-600 font-bold text-base">
                    <span>Annuler la Facture {{ $invoice->reference }}</span>
                </div>
                <p class="text-xs text-slate-600">
                    Cette opération est réservée aux factures sans aucun encaissement enregistré.
                </p>

                <form action="{{ route('invoices.cancel', $invoice) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="cancel_reason" class="block text-xs font-semibold text-slate-700 mb-1">Motif d'annulation *</label>
                        <textarea id="cancel_reason" name="reason" rows="3" required placeholder="Erreur de saisie de prestations..." class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg"></textarea>
                    </div>

                    <div class="flex items-center justify-end space-x-2 pt-2 border-t border-slate-100">
                        <button type="button" @click="cancelInvoiceModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800">
                            Fermer
                        </button>
                        <button type="submit" class="px-4 py-2 text-xs font-semibold bg-rose-600 hover:bg-rose-700 text-white rounded-lg transition">
                            Confirmer l'Annulation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
