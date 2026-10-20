<x-layouts.app>
    <x-slot:title>Enregistrer un Appareil</x-slot:title>

    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Nouvel Appareil</h1>
                <p class="text-sm text-slate-500 mt-0.5">Enregistrer un équipement déposé à l'atelier</p>
            </div>
            <a href="{{ route('devices.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                &larr; Retour à la liste
            </a>
        </div>

        <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-xs">
            <form action="{{ route('devices.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="customer_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Client propriétaire *
                    </label>
                    <select id="customer_id" name="customer_id" required class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                        <option value="">Sélectionnez un client existant...</option>
                        @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ (old('customer_id', $selectedCustomerId) == $c->id) ? 'selected' : '' }}>
                            {{ $c->name }} ({{ $c->phone }})
                        </option>
                        @endforeach
                    </select>
                    <div class="text-[11px] text-slate-400 mt-1">
                        Le client n'est pas encore enregistré ?
                        <a href="{{ route('customers.create') }}" class="text-blue-600 underline font-medium">Créer d'abord la fiche client</a>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="category" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Catégorie *</label>
                        <select id="category" name="category" required class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                            <option value="laptop" {{ old('category') === 'laptop' ? 'selected' : '' }}>Ordinateur portable</option>
                            <option value="desktop" {{ old('category') === 'desktop' ? 'selected' : '' }}>Ordinateur de bureau / Fixe</option>
                            <option value="printer" {{ old('category') === 'printer' ? 'selected' : '' }}>Imprimante</option>
                            <option value="network" {{ old('category') === 'network' ? 'selected' : '' }}>Équipement réseau</option>
                            <option value="other" {{ old('category') === 'other' ? 'selected' : '' }}>Autre équipement</option>
                        </select>
                    </div>

                    <div>
                        <label for="brand" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Marque *</label>
                        <input id="brand" type="text" name="brand" value="{{ old('brand') }}" required
                               placeholder="ex: HP, Dell, Lenovo, Apple..."
                               class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="model" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Modèle précis *</label>
                        <input id="model" type="text" name="model" value="{{ old('model') }}" required
                               placeholder="ex: ProBook 450 G8, Inspiron 15..."
                               class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                    </div>

                    <div>
                        <label for="serial_number" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Numéro de série / Service Tag</label>
                        <input id="serial_number" type="text" name="serial_number" value="{{ old('serial_number') }}"
                               placeholder="ex: 5CD1234567"
                               class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                    </div>
                </div>

                <div>
                    <label for="aesthetic_condition" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        État esthétique à l'entrée
                    </label>
                    <textarea id="aesthetic_condition" name="aesthetic_condition" rows="2"
                              placeholder="ex: Charnière gauche fragilisée, rayure profonde sur le capot, vis manquante..."
                              class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">{{ old('aesthetic_condition') }}</textarea>
                </div>

                <div>
                    <label for="accessories" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Accessoires déposés avec l'appareil
                    </label>
                    <input id="accessories" type="text" name="accessories" value="{{ old('accessories') }}"
                           placeholder="ex: Chargeur original 65W, sacoche noire HP, souris sans fil..."
                           class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                </div>

                <div>
                    <label for="notes" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Observations techniques complémentaires
                    </label>
                    <textarea id="notes" name="notes" rows="2"
                              class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">{{ old('notes') }}</textarea>
                </div>

                <div class="pt-4 border-t border-slate-200 flex items-center justify-end space-x-3">
                    <a href="{{ route('devices.index') }}" class="px-4 py-2 text-sm font-medium text-slate-700 hover:text-slate-900">
                        Annuler
                    </a>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-xl shadow-sm transition">
                        Enregistrer l'appareil
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
