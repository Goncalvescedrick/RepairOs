<x-layouts.app>
    <x-slot:title>Nouveau Client</x-slot:title>

    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Ajouter un Client</h1>
                <p class="text-sm text-slate-500 mt-0.5">Enregistrer les coordonnées du propriétaire d'équipements</p>
            </div>
            <a href="{{ route('customers.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                &larr; Retour à la liste
            </a>
        </div>

        <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-xs">
            <form action="{{ route('customers.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Nom complet ou Raison sociale *
                    </label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                           placeholder="ex: Dr. Zongo Michel, Entreprise Sahel Tech..."
                           class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                </div>

                <div>
                    <label for="phone" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Numéro de téléphone principal (WhatsApp) *
                    </label>
                    <input id="phone" type="text" name="phone" value="{{ old('phone') }}" required
                           placeholder="ex: +226 70 12 34 56 ou 76112233"
                           class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                    <p class="text-[11px] text-slate-400 mt-1">Utilisé pour l'envoi des notifications et du lien de suivi WhatsApp.</p>
                </div>

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Adresse e-mail (facultatif)
                    </label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                           placeholder="client@domaine.bf"
                           class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                </div>

                <div>
                    <label for="address" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Adresse physique / Quartier (facultatif)
                    </label>
                    <input id="address" type="text" name="address" value="{{ old('address') }}"
                           placeholder="ex: Ouaga 2000, Secteur 15, Rue 12.45..."
                           class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                </div>

                <div>
                    <label for="notes" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Notes internes sur le client (facultatif)
                    </label>
                    <textarea id="notes" name="notes" rows="3"
                              placeholder="Préférences de facturation, historique particulier..."
                              class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">{{ old('notes') }}</textarea>
                </div>

                <div class="pt-4 border-t border-slate-200 flex items-center justify-end space-x-3">
                    <a href="{{ route('customers.index') }}" class="px-4 py-2 text-sm font-medium text-slate-700 hover:text-slate-900">
                        Annuler
                    </a>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-xl shadow-sm transition">
                        Enregistrer le client
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
