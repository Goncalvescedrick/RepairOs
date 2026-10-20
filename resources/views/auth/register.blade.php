<x-layouts.guest>
    <x-slot:title>Créer votre Atelier</x-slot:title>

    <div class="mb-5 text-center">
        <h2 class="text-xl font-bold text-slate-900">Nouvel Atelier RepairOS</h2>
        <p class="text-xs text-slate-500 mt-1">Créez votre espace de travail en 1 minute</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <!-- Section Atelier -->
        <div class="bg-blue-50/60 p-3.5 rounded-xl border border-blue-100 mb-3 space-y-3">
            <div class="text-xs font-bold text-blue-900 uppercase tracking-wider">Votre Atelier</div>
            
            <div>
                <label for="workshop_name" class="block text-xs font-medium text-slate-700 mb-1">Nom de l'atelier / Entreprise *</label>
                <input id="workshop_name" type="text" name="workshop_name" value="{{ old('workshop_name') }}" required
                       placeholder="ex: FASO PC REPAIR, Clinique Micro-Info..."
                       class="w-full px-3 py-1.5 text-sm bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label for="city" class="block text-xs font-medium text-slate-700 mb-1">Ville</label>
                    <input id="city" type="text" name="city" value="{{ old('city', 'Ouagadougou') }}"
                           class="w-full px-3 py-1.5 text-sm bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>
                <div>
                    <label for="currency" class="block text-xs font-medium text-slate-700 mb-1">Devise</label>
                    <input id="currency" type="text" name="currency" value="{{ old('currency', 'XOF') }}" readonly
                           class="w-full px-3 py-1.5 text-sm bg-slate-100 border border-slate-300 rounded-lg text-slate-600 font-semibold cursor-not-allowed">
                </div>
            </div>
        </div>

        <!-- Section Responsable -->
        <div class="space-y-3">
            <div class="text-xs font-bold text-slate-700 uppercase tracking-wider">Responsable / Administrateur</div>

            <div>
                <label for="name" class="block text-xs font-medium text-slate-700 mb-1">Nom complet *</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required
                       placeholder="ex: Traoré Idrissa"
                       class="w-full px-3.5 py-1.5 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
            </div>

            <div>
                <label for="phone" class="block text-xs font-medium text-slate-700 mb-1">Téléphone mobile (WhatsApp) *</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone') }}" required
                       placeholder="ex: +226 70 00 11 22 ou 70112233"
                       class="w-full px-3.5 py-1.5 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
            </div>

            <div>
                <label for="email" class="block text-xs font-medium text-slate-700 mb-1">Adresse E-mail *</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required
                       placeholder="idrissa@atelier.bf"
                       class="w-full px-3.5 py-1.5 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label for="password" class="block text-xs font-medium text-slate-700 mb-1">Mot de passe *</label>
                    <input id="password" type="password" name="password" required placeholder="8 car. min."
                           class="w-full px-3 py-1.5 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-xs font-medium text-slate-700 mb-1">Confirmation *</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required placeholder="Confirmer"
                           class="w-full px-3 py-1.5 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                </div>
            </div>
        </div>

        <button type="submit" class="w-full mt-2 py-2.5 px-4 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm shadow-sm transition">
            Créer mon atelier & démarrer
        </button>

        <div class="pt-3 border-t border-slate-200 text-center text-xs text-slate-600">
            Déjà inscrit ?
            <a href="{{ route('login') }}" class="font-semibold text-blue-600 hover:text-blue-700 underline">Connectez-vous</a>
        </div>
    </form>
</x-layouts.guest>
