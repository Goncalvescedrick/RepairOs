<x-layouts.app>
    <x-slot:title>Modifier Client — {{ $customer->name }}</x-slot:title>

    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Modifier Client</h1>
                <p class="text-sm text-slate-500 mt-0.5">Mettre à jour les informations de {{ $customer->name }}</p>
            </div>
            <a href="{{ route('customers.show', $customer) }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                &larr; Retour à la fiche
            </a>
        </div>

        <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-xs">
            <form action="{{ route('customers.update', $customer) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Nom complet ou Raison sociale *
                    </label>
                    <input id="name" type="text" name="name" value="{{ old('name', $customer->name) }}" required
                           class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                </div>

                <div>
                    <label for="phone" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Numéro de téléphone principal (WhatsApp) *
                    </label>
                    <input id="phone" type="text" name="phone" value="{{ old('phone', $customer->phone) }}" required
                           class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                </div>

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Adresse e-mail (facultatif)
                    </label>
                    <input id="email" type="email" name="email" value="{{ old('email', $customer->email) }}"
                           class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                </div>

                <div>
                    <label for="address" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Adresse physique / Quartier (facultatif)
                    </label>
                    <input id="address" type="text" name="address" value="{{ old('address', $customer->address) }}"
                           class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                </div>

                <div>
                    <label for="notes" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Notes internes sur le client (facultatif)
                    </label>
                    <textarea id="notes" name="notes" rows="3"
                              class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">{{ old('notes', $customer->notes) }}</textarea>
                </div>

                <div class="pt-4 border-t border-slate-200 flex items-center justify-between">
                    @can('delete', $customer)
                    <form action="{{ route('customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('Confirmez-vous l’archivage de ce client ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs font-semibold text-rose-600 hover:text-rose-800">
                            Archiver ce client
                        </button>
                    </form>
                    @else
                    <div></div>
                    @endcan

                    <div class="flex items-center space-x-3">
                        <a href="{{ route('customers.show', $customer) }}" class="px-4 py-2 text-sm font-medium text-slate-700 hover:text-slate-900">
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
