<x-layouts.app>
    <x-slot:title>Modifier l'Appareil — {{ $device->brand }} {{ $device->model }}</x-slot:title>

    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Modifier l'Appareil</h1>
                <p class="text-sm text-slate-500 mt-0.5">Mettre à jour les informations de l'équipement</p>
            </div>
            <a href="{{ route('devices.show', $device) }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                &larr; Retour à la fiche
            </a>
        </div>

        <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-xs">
            <form action="{{ route('devices.update', $device) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="category" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Catégorie *</label>
                        <select id="category" name="category" required class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                            <option value="laptop" {{ old('category', $device->category) === 'laptop' ? 'selected' : '' }}>Ordinateur portable</option>
                            <option value="desktop" {{ old('category', $device->category) === 'desktop' ? 'selected' : '' }}>Ordinateur de bureau / Fixe</option>
                            <option value="printer" {{ old('category', $device->category) === 'printer' ? 'selected' : '' }}>Imprimante</option>
                            <option value="network" {{ old('category', $device->category) === 'network' ? 'selected' : '' }}>Équipement réseau</option>
                            <option value="other" {{ old('category', $device->category) === 'other' ? 'selected' : '' }}>Autre équipement</option>
                        </select>
                    </div>

                    <div>
                        <label for="brand" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Marque *</label>
                        <input id="brand" type="text" name="brand" value="{{ old('brand', $device->brand) }}" required
                               class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="model" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Modèle précis *</label>
                        <input id="model" type="text" name="model" value="{{ old('model', $device->model) }}" required
                               class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                    </div>

                    <div>
                        <label for="serial_number" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Numéro de série / Tag</label>
                        <input id="serial_number" type="text" name="serial_number" value="{{ old('serial_number', $device->serial_number) }}"
                               class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                    </div>
                </div>

                <div>
                    <label for="aesthetic_condition" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        État esthétique
                    </label>
                    <textarea id="aesthetic_condition" name="aesthetic_condition" rows="2"
                              class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">{{ old('aesthetic_condition', $device->aesthetic_condition) }}</textarea>
                </div>

                <div>
                    <label for="accessories" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Accessoires déposés
                    </label>
                    <input id="accessories" type="text" name="accessories" value="{{ old('accessories', $device->accessories) }}"
                           class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                </div>

                <div>
                    <label for="notes" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Observations
                    </label>
                    <textarea id="notes" name="notes" rows="2"
                              class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">{{ old('notes', $device->notes) }}</textarea>
                </div>

                <div class="pt-4 border-t border-slate-200 flex items-center justify-between">
                    @can('delete', $device)
                    <form action="{{ route('devices.destroy', $device) }}" method="POST" onsubmit="return confirm('Confirmez-vous l’archivage de cet appareil ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs font-semibold text-rose-600 hover:text-rose-800">
                            Archiver cet appareil
                        </button>
                    </form>
                    @else
                    <div></div>
                    @endcan

                    <div class="flex items-center space-x-3">
                        <a href="{{ route('devices.show', $device) }}" class="px-4 py-2 text-sm font-medium text-slate-700 hover:text-slate-900">
                            Annuler
                        </a>
                        <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-xl shadow-sm transition">
                            Enregistrer les modifications
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
