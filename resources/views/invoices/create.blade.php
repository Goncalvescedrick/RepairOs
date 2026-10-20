<x-layouts.app>
    <x-slot:title>Émettre une Facture</x-slot:title>

    <div class="max-w-4xl mx-auto space-y-6" x-data="{
        taxRate: {{ old('tax_rate', $tenant->tax_rate ?: 0) }},
        discount: {{ old('discount_amount', 0) }},
        items: [
            { type: 'SERVICE', description: 'Prestation de réparation', quantity: 1, unit_price: 25000 },
        ],
        addItem() {
            this.items.push({ type: 'PART', description: '', quantity: 1, unit_price: 0 });
        },
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },
        subtotal() {
            return this.items.reduce((sum, item) => sum + ((parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0)), 0);
        },
        taxAmount() {
            return (this.subtotal() * (parseFloat(this.taxRate) || 0)) / 100;
        },
        total() {
            return Math.max(0, this.subtotal() - (parseFloat(this.discount) || 0) + this.taxAmount());
        },
        format(val) {
            return Math.round(val).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        }
    }">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Émettre une Facture</h1>
                <p class="text-sm text-slate-500 mt-0.5">Émission directe d'une facture pour un ticket de réparation</p>
            </div>
            <a href="{{ route('invoices.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                &larr; Retour aux factures
            </a>
        </div>

        <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-xs">
            <form action="{{ route('invoices.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Sélection Ticket -->
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                    <label for="repair_ticket_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Ticket de réparation concerné *
                    </label>
                    <select id="repair_ticket_id" name="repair_ticket_id" required class="w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        <option value="">Sélectionnez un ticket...</option>
                        @foreach($tickets as $t)
                        <option value="{{ $t->id }}" {{ (old('repair_ticket_id', $ticket?->id) == $t->id) ? 'selected' : '' }}>
                            {{ $t->reference }} — {{ $t->customer->name }} ({{ $t->device->brand }} {{ $t->device->model }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Lignes Facturées (Table Alpine.js) -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Lignes facturées (Services & Pièces)</span>
                        <button type="button" @click="addItem()" class="inline-flex items-center text-xs font-semibold text-blue-600 hover:text-blue-800 bg-blue-50 px-3 py-1 rounded-lg border border-blue-200 transition">
                            + Ajouter une ligne
                        </button>
                    </div>

                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-2.5 w-32">Type</th>
                                    <th class="px-4 py-2.5">Désignation</th>
                                    <th class="px-4 py-2.5 w-24 text-center">Quantité</th>
                                    <th class="px-4 py-2.5 w-36 text-right">Prix Unit. ({{ $tenant->currency }})</th>
                                    <th class="px-4 py-2.5 w-36 text-right">Total Ligne</th>
                                    <th class="px-3 py-2.5 w-10 text-center"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="(item, index) in items" :key="index">
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="px-4 py-2">
                                            <select :name="`items[${index}][type]`" x-model="item.type" class="w-full py-1.5 px-2 bg-slate-50 border border-slate-200 rounded text-xs">
                                                <option value="SERVICE">Service</option>
                                                <option value="PART">Pièce</option>
                                                <option value="OTHER">Autre</option>
                                            </select>
                                        </td>
                                        <td class="px-4 py-2">
                                            <input type="text" :name="`items[${index}][description]`" x-model="item.description" required class="w-full py-1.5 px-2 bg-slate-50 border border-slate-200 rounded text-xs">
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <input type="number" step="0.5" min="0.5" :name="`items[${index}][quantity]`" x-model.number="item.quantity" required class="w-full py-1.5 px-2 bg-slate-50 border border-slate-200 rounded text-xs text-center">
                                        </td>
                                        <td class="px-4 py-2 text-right">
                                            <input type="number" step="100" min="0" :name="`items[${index}][unit_price]`" x-model.number="item.unit_price" required class="w-full py-1.5 px-2 bg-slate-50 border border-slate-200 rounded text-xs text-right">
                                        </td>
                                        <td class="px-4 py-2 text-right font-bold text-slate-800" x-text="format(item.quantity * item.unit_price)"></td>
                                        <td class="px-3 py-2 text-center">
                                            <button type="button" @click="removeItem(index)" class="text-rose-500 hover:text-rose-700 text-base">&times;</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Récapitulatif Financier -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-200">
                    <div class="space-y-3">
                        <div>
                            <label for="due_date" class="block text-xs font-semibold text-slate-700 mb-1">Date d'échéance du règlement</label>
                            <input id="due_date" type="date" name="due_date" value="{{ old('due_date', now()->addDays(30)->format('Y-m-d')) }}" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg">
                        </div>

                        <div>
                            <label for="notes" class="block text-xs font-semibold text-slate-700 mb-1">Notes ou mentions sur la facture</label>
                            <textarea id="notes" name="notes" rows="3" placeholder="Modalités de règlement..." class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2.5 text-sm">
                        <div class="flex items-center justify-between text-slate-600">
                            <span>Sous-total HT :</span>
                            <span class="font-bold text-slate-900"><span x-text="format(subtotal())"></span> {{ $tenant->currency }}</span>
                        </div>

                        <div class="flex items-center justify-between text-slate-600">
                            <span>Remise ({{ $tenant->currency }}) :</span>
                            <input type="number" name="discount_amount" x-model.number="discount" min="0" step="500" class="w-32 py-1 px-2 text-right text-xs bg-white border border-slate-300 rounded">
                        </div>

                        <div class="flex items-center justify-between text-slate-600">
                            <span>TVA (%):</span>
                            <input type="number" name="tax_rate" x-model.number="taxRate" min="0" max="100" step="0.5" class="w-20 py-1 px-2 text-right text-xs bg-white border border-slate-300 rounded">
                        </div>

                        <div class="flex items-center justify-between text-slate-600 text-xs">
                            <span>Montant TVA :</span>
                            <span><span x-text="format(taxAmount())"></span> {{ $tenant->currency }}</span>
                        </div>

                        <div class="pt-2 border-t border-slate-200 flex items-center justify-between text-base font-extrabold text-slate-900">
                            <span>Total Net à Payer :</span>
                            <span class="text-blue-700 text-lg"><span x-text="format(total())"></span> {{ $tenant->currency }}</span>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-200 flex items-center justify-end space-x-3">
                    <a href="{{ route('invoices.index') }}" class="px-4 py-2 text-sm font-medium text-slate-700 hover:text-slate-900">
                        Annuler
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-xl shadow-sm transition">
                        Émettre la facture
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
