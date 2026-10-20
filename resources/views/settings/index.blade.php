<x-app-layout title="Paramètres de l'Atelier">
    <div class="space-y-6 max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Paramètres de l'Atelier</h1>
                <p class="text-sm text-slate-500 mt-1">Configurez l'identité, les coordonnées, les mentions légales et la devise de votre espace.</p>
            </div>
            @if(auth()->user()->isOwnerOf($tenant->id))
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                    Propriétaire principal
                </span>
            @endif
        </div>

        <form method="POST" action="{{ route('settings.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Coordonnées & Identité -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-base font-bold text-slate-900">Identité commerciale & Coordonnées</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Ces informations apparaîtront sur tous les bons de dépôt, devis et factures émis.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nom de l'atelier / Raison sociale *</label>
                        <input type="text" name="name" value="{{ old('name', $tenant->name) }}" required class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Téléphone professionnel</label>
                        <input type="text" name="phone" value="{{ old('phone', $tenant->phone) }}" placeholder="+226 70 00 00 00" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email de contact</label>
                        <input type="email" name="email" value="{{ old('email', $tenant->email) }}" placeholder="contact@monatelier.bf" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Adresse physique</label>
                        <input type="text" name="address" value="{{ old('address', $tenant->address) }}" placeholder="Ex : Rue 12.45, Porte 34, Secteur 15" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Ville *</label>
                        <input type="text" name="city" value="{{ old('city', $tenant->city) }}" required placeholder="Ouagadougou" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Code pays (ISO 2 lettres) *</label>
                        <input type="text" name="country_code" value="{{ old('country_code', $tenant->country_code) }}" required maxlength="2" placeholder="BF" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 uppercase font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <!-- Devise, Fuseau horaire & Fiscalité -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-base font-bold text-slate-900">Devise, Fuseau horaire & Fiscalité</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Paramètres régionaux appliqués aux calculs et aux dates de l'atelier.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Devise par défaut *</label>
                        <input type="text" name="currency" value="{{ old('currency', $tenant->currency) }}" required placeholder="XOF" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 uppercase font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <span class="text-[11px] text-slate-400 mt-1 block">Exemple : XOF (FCFA), EUR, USD</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Fuseau horaire *</label>
                        <select name="timezone" required class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="Africa/Ouagadougou" {{ old('timezone', $tenant->timezone) === 'Africa/Ouagadougou' ? 'selected' : '' }}>Africa/Ouagadougou (UTC+0)</option>
                            <option value="Africa/Abidjan" {{ old('timezone', $tenant->timezone) === 'Africa/Abidjan' ? 'selected' : '' }}>Africa/Abidjan (UTC+0)</option>
                            <option value="Africa/Dakar" {{ old('timezone', $tenant->timezone) === 'Africa/Dakar' ? 'selected' : '' }}>Africa/Dakar (UTC+0)</option>
                            <option value="Africa/Bamako" {{ old('timezone', $tenant->timezone) === 'Africa/Bamako' ? 'selected' : '' }}>Africa/Bamako (UTC+0)</option>
                            <option value="Africa/Lome" {{ old('timezone', $tenant->timezone) === 'Africa/Lome' ? 'selected' : '' }}>Africa/Lomé (UTC+0)</option>
                            <option value="Africa/Cotonou" {{ old('timezone', $tenant->timezone) === 'Africa/Cotonou' ? 'selected' : '' }}>Africa/Cotonou (UTC+1)</option>
                            <option value="Africa/Douala" {{ old('timezone', $tenant->timezone) === 'Africa/Douala' ? 'selected' : '' }}>Africa/Douala (UTC+1)</option>
                            <option value="Europe/Paris" {{ old('timezone', $tenant->timezone) === 'Europe/Paris' ? 'selected' : '' }}>Europe/Paris (UTC+1/+2)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Taux de TVA / Taxe locale (%) *</label>
                        <input type="number" step="0.01" min="0" max="100" name="tax_rate" value="{{ old('tax_rate', $tenant->tax_rate) }}" required class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <span class="text-[11px] text-slate-400 mt-1 block">0 pour franchise en base de TVA</span>
                    </div>
                </div>
            </div>

            <!-- Numérotation des pièces -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-base font-bold text-slate-900">Préfixes de numérotation automatique</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Personnalisez les identifiants séquentiels générés pour vos documents.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Préfixe Tickets *</label>
                        <input type="text" name="ticket_prefix" value="{{ old('ticket_prefix', $tenant->ticket_prefix) }}" required maxlength="10" placeholder="TICK" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 uppercase font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <span class="text-[11px] text-slate-400 mt-1 block">Format : TICK-2026-0001</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Préfixe Devis *</label>
                        <input type="text" name="quote_prefix" value="{{ old('quote_prefix', $tenant->quote_prefix) }}" required maxlength="10" placeholder="DEV" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 uppercase font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <span class="text-[11px] text-slate-400 mt-1 block">Format : DEV-2026-0001</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Préfixe Factures *</label>
                        <input type="text" name="invoice_prefix" value="{{ old('invoice_prefix', $tenant->invoice_prefix) }}" required maxlength="10" placeholder="FAC" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 uppercase font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <span class="text-[11px] text-slate-400 mt-1 block">Format : FAC-2026-0001</span>
                    </div>
                </div>
            </div>

            <!-- Mentions Légales & Conditions de garde -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-base font-bold text-slate-900">Mentions légales & Conditions de service</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Texte contractuel imprimé au bas des devis et des factures (délais de garde, garantie des pièces, etc.).</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Conditions affichées sur les documents</label>
                    <textarea name="legal_notices" rows="4" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 leading-relaxed" placeholder="Ex : Tout appareil non récupéré sous 90 jours sera considéré comme abandonné. Les pièces remplacées sont garanties 3 mois...">{{ old('legal_notices', $tenant->legal_notices) }}</textarea>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3">
                <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold bg-blue-600 text-white hover:bg-blue-700 shadow-sm transition-colors">
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
