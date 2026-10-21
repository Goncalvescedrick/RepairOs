<x-layouts.app>
    <x-slot:title>Nouveau Ticket de Réparation</x-slot:title>

    <div class="max-w-3xl mx-auto space-y-6" x-data="{
        selectedCustomerId: '{{ old('customer_id', $selectedCustomerId) }}',
        selectedDeviceId: '{{ old('device_id', $selectedDeviceId) }}',
        devices: @js(\App\Models\Device::select('id', 'customer_id', 'brand', 'model', 'serial_number', 'category')->get()),
        filteredDevices() {
            if (!this.selectedCustomerId) return [];
            return this.devices.filter(d => d.customer_id == this.selectedCustomerId);
        }
    }">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Ouvrir un Ticket de Réparation</h1>
                <p class="text-sm text-slate-500 mt-0.5">Enregistrer la prise en charge d'un équipement en atelier</p>
            </div>
            <a href="{{ route('tickets.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                &larr; Retour aux tickets
            </a>
        </div>

        <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-xs">
            <form action="{{ route('tickets.store') }}" method="POST" class="space-y-5">
                @csrf

                <!-- 1. Sélection Client & Appareil -->
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-4">
                    <div class="text-xs font-bold text-slate-700 uppercase tracking-wider">Client & Appareil Déposé</div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="customer_id" class="block text-xs font-semibold text-slate-700 mb-1">Client *</label>
                            <select id="customer_id" name="customer_id" x-model="selectedCustomerId" required
                                    class="w-full px-3 py-2 text-sm bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                                <option value="">Sélectionnez un client...</option>
                                @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ (old('customer_id', $selectedCustomerId) == $c->id) ? 'selected' : '' }}>
                                    {{ $c->name }} ({{ $c->phone }})
                                </option>
                                @endforeach
                            </select>
                            <div class="text-[11px] text-slate-400 mt-1">
                                Pas encore dans la liste ?
                                <a href="{{ route('customers.create') }}" class="text-blue-600 underline font-medium">Créer nouveau client</a>
                            </div>
                        </div>

                        <div>
                            <label for="device_id" class="block text-xs font-semibold text-slate-700 mb-1">Appareil concerné *</label>
                            <select id="device_id" name="device_id" x-model="selectedDeviceId" required
                                    class="w-full px-3 py-2 text-sm bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                                    :disabled="!selectedCustomerId">
                                <option value="">Sélectionnez l'appareil du client...</option>
                                <template x-for="dev in filteredDevices()" :key="dev.id">
                                    <option :value="dev.id" x-text="`${dev.brand} ${dev.model} (${dev.serial_number || 'Sans S/N'})`" :selected="dev.id == selectedDeviceId"></option>
                                </template>
                            </select>
                            <div class="text-[11px] text-slate-400 mt-1" x-show="selectedCustomerId">
                                <a :href="`{{ route('devices.create') }}?customer_id=${selectedCustomerId}`" class="text-blue-600 underline font-medium">
                                    + Enregistrer un nouvel appareil pour ce client
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Symptômes & Panne constatée -->
                <div class="space-y-4">
                    <div class="text-xs font-bold text-slate-700 uppercase tracking-wider">Description de la Panne</div>

                    <div>
                        <label for="reported_issue" class="block text-xs font-semibold text-slate-700 mb-1">
                            Problème déclaré par le client *
                        </label>
                        <textarea id="reported_issue" name="reported_issue" rows="3" required
                                  placeholder="ex: L'ordinateur s'éteint tout seul après 10 minutes d'utilisation. Chauffage excessif..."
                                  class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">{{ old('reported_issue') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="intake_condition" class="block text-xs font-semibold text-slate-700 mb-1">
                                État constaté à la réception
                            </label>
                            <input id="intake_condition" type="text" name="intake_condition" value="{{ old('intake_condition') }}"
                                   placeholder="ex: Écran intact, rayures sur châssis, batterie absente..."
                                   class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                        </div>

                        <div>
                            <label for="accessories_received" class="block text-xs font-semibold text-slate-700 mb-1">
                                Accessoires reçus
                            </label>
                            <input id="accessories_received" type="text" name="accessories_received" value="{{ old('accessories_received') }}"
                                   placeholder="ex: Chargeur, souris, adaptateur HDMI..."
                                   class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                        </div>
                    </div>

                    <!-- Grille d'Inspection Visuelle Rapide -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Grille d'Inspection Visuelle à l'Entrée</span>
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-blue-100 text-blue-800">Anti-litige client</span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Écran / Dalle</label>
                                <select name="intake_checklist[screen]" class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                                    <option value="Intact">Intact</option>
                                    <option value="Micro-rayures">Micro-rayures</option>
                                    <option value="Fissuré / Cassé">Fissuré / Cassé</option>
                                    <option value="Non testable (Éteint)">Non testable (Éteint)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Clavier / Pavé tactile</label>
                                <select name="intake_checklist[keyboard]" class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                                    <option value="Complet & Fonctionnel">Complet & Fonctionnel</option>
                                    <option value="Touches manquantes/usées">Touches manquantes/usées</option>
                                    <option value="Défectueux">Défectueux</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Châssis & Charnières</label>
                                <select name="intake_checklist[chassis]" class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                                    <option value="Bon état">Bon état</option>
                                    <option value="Rayures / Chocs">Rayures / Chocs</option>
                                    <option value="Charnière cassée">Charnière cassée</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Batterie</label>
                                <select name="intake_checklist[battery]" class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                                    <option value="Présente">Présente</option>
                                    <option value="Absente">Absente</option>
                                    <option value="Gonflée / HS">Gonflée / HS</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Ports & Connecteurs</label>
                                <select name="intake_checklist[ports]" class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                                    <option value="RAS">RAS</option>
                                    <option value="Connecteur charge abîmé">Connecteur charge abîmé</option>
                                    <option value="Port USB/HDMI endommagé">Port USB/HDMI endommagé</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Allumage au comptoir</label>
                                <select name="intake_checklist[power]" class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                                    <option value="S'allume">S'allume</option>
                                    <option value="Ne s'allume pas">Ne s'allume pas</option>
                                    <option value="Démarre sans affichage">Démarre sans affichage</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Paramètres opérationnels -->
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-4">
                    <div class="text-xs font-bold text-slate-700 uppercase tracking-wider">Planification & Affectation</div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="priority" class="block text-xs font-semibold text-slate-700 mb-1">Niveau de Priorité *</label>
                            <select id="priority" name="priority" required class="w-full px-3 py-2 text-sm bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                                <option value="NORMALE" {{ old('priority', 'NORMALE') === 'NORMALE' ? 'selected' : '' }}>Normale</option>
                                <option value="BASSE" {{ old('priority') === 'BASSE' ? 'selected' : '' }}>Basse</option>
                                <option value="HAUTE" {{ old('priority') === 'HAUTE' ? 'selected' : '' }}>Haute</option>
                                <option value="URGENTE" {{ old('priority') === 'URGENTE' ? 'selected' : '' }}>Urgente</option>
                            </select>
                        </div>

                        <div>
                            <label for="assigned_to_user_id" class="block text-xs font-semibold text-slate-700 mb-1">Technicien assigné</label>
                            <select id="assigned_to_user_id" name="assigned_to_user_id" class="w-full px-3 py-2 text-sm bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                                <option value="">Non assigné (à déterminer)</option>
                                @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}" {{ old('assigned_to_user_id') == $tech->id ? 'selected' : '' }}>
                                    {{ $tech->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="deposit_date" class="block text-xs font-semibold text-slate-700 mb-1">Date & Heure de dépôt *</label>
                            <input id="deposit_date" type="datetime-local" name="deposit_date"
                                   value="{{ old('deposit_date', now()->format('Y-m-d\TH:i')) }}" required
                                   class="w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        </div>

                        <div>
                            <label for="estimated_completion_date" class="block text-xs font-semibold text-slate-700 mb-1">Date estimée de fin (facultatif)</label>
                            <input id="estimated_completion_date" type="datetime-local" name="estimated_completion_date"
                                   value="{{ old('estimated_completion_date') }}"
                                   class="w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-200/80">
                        <div>
                            <label for="device_access_code" class="block text-xs font-semibold text-slate-700 mb-1">
                                Code PIN / Mot de passe session (Coffre-fort temporaire)
                            </label>
                            <input id="device_access_code" type="text" name="device_access_code" value="{{ old('device_access_code') }}"
                                   placeholder="Chiffré AES-256 et purgé automatiquement à la restitution"
                                   class="w-full px-3.5 py-2 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        </div>
                        <div>
                            <label for="warranty_days" class="block text-xs font-semibold text-slate-700 mb-1">
                                Garantie SAV après restitution (jours)
                            </label>
                            <input id="warranty_days" type="number" min="0" max="1095" name="warranty_days" value="{{ old('warranty_days', 30) }}"
                                   class="w-full px-3.5 py-2 text-xs bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        </div>
                    </div>
                </div>

                <!-- 4. Notes -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="internal_notes" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Notes internes (Confidentiel atelier)
                        </label>
                        <textarea id="internal_notes" name="internal_notes" rows="2"
                                  placeholder="Remarques pour les techniciens uniquement..."
                                  class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">{{ old('internal_notes') }}</textarea>
                    </div>

                    <div>
                        <label for="client_notes" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Notes destinées au client (visible sur reçu)
                        </label>
                        <textarea id="client_notes" name="client_notes" rows="2"
                                  placeholder="Consignes données au client..."
                                  class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">{{ old('client_notes') }}</textarea>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-200 flex items-center justify-end space-x-3">
                    <a href="{{ route('tickets.index') }}" class="px-4 py-2 text-sm font-medium text-slate-700 hover:text-slate-900">
                        Annuler
                    </a>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-xl shadow-sm transition">
                        Créer le ticket & générer bon de dépôt
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
