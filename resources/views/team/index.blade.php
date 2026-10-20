<x-app-layout title="Équipe & Collaborateurs">
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Gestion de l'équipe</h1>
                <p class="text-sm text-slate-500 mt-1">Gérez les accès, rôles et techniciens de l'atelier <strong>{{ $tenant->name }}</strong>.</p>
            </div>
            <div>
                <button type="button" @click="$dispatch('open-modal', 'add-member-modal')" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-semibold bg-blue-600 text-white hover:bg-blue-700 shadow-sm transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    Ajouter un collaborateur
                </button>
            </div>
        </div>

        <!-- Team Members Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-900 text-sm">Membres actifs et autorisations</h3>
                <span class="text-xs text-slate-400 font-medium">{{ $memberships->count() }} collaborateur(s)</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider font-semibold text-slate-500 border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-3.5">Collaborateur</th>
                            <th class="px-6 py-3.5">Téléphone</th>
                            <th class="px-6 py-3.5">Rôle</th>
                            <th class="px-6 py-3.5">Statut</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($memberships as $membership)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-9 h-9 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-slate-700 text-xs shrink-0">
                                            {{ strtoupper(substr($membership->user->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900">
                                                {{ $membership->user->name }}
                                                @if($membership->user_id === auth()->id())
                                                    <span class="ml-1.5 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">Vous</span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-slate-400">{{ $membership->user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-xs font-mono text-slate-600">
                                    {{ $membership->user->phone ?: 'Non renseigné' }}
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $roleEnum = $membership->getRoleEnum();
                                        $roleBadge = match($membership->role) {
                                            'owner' => 'bg-purple-50 text-purple-700 border-purple-200',
                                            'admin' => 'bg-blue-50 text-blue-700 border-blue-200',
                                            default => 'bg-slate-100 text-slate-700 border-slate-200',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $roleBadge }}">
                                        {{ $roleEnum?->label() ?? ucfirst($membership->role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($membership->is_active)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                            Actif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1.5"></span>
                                            Désactivé
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        @if($membership->role !== 'owner' || $membership->user_id !== auth()->id())
                                            <!-- Toggle status form -->
                                            <form method="POST" action="{{ route('team.toggle-status', $membership) }}" onsubmit="return confirm('Confirmer la modification du statut d\'accès de ce membre ?')">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-medium border {{ $membership->is_active ? 'border-amber-200 text-amber-700 hover:bg-amber-50' : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50' }} transition-colors">
                                                    {{ $membership->is_active ? 'Désactiver' : 'Activer' }}
                                                </button>
                                            </form>

                                            <!-- Role update form (only for non-owners) -->
                                            @if($membership->role !== 'owner')
                                                <form method="POST" action="{{ route('team.update', $membership) }}" class="inline-flex">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="role" value="{{ $membership->role === 'admin' ? 'technician' : 'admin' }}">
                                                    <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-medium border border-slate-200 text-slate-700 hover:bg-slate-50 transition-colors" title="Changer le rôle">
                                                        Passer en {{ $membership->role === 'admin' ? 'Technicien' : 'Admin' }}
                                                    </button>
                                                </form>
                                            @endif
                                        @else
                                            <span class="text-xs text-slate-400 italic">Propriétaire principal</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Member Modal -->
        <div x-data="{ open: false }" @open-modal.window="if ($event.detail === 'add-member-modal') open = true" x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 py-6 text-center sm:block sm:p-0">
                <div x-show="open" @click="open = false" class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm"></div>

                <div x-show="open" class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl border border-slate-200">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <h3 class="text-lg font-bold text-slate-900">Nouveau collaborateur</h3>
                        <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('team.store') }}" class="mt-5 space-y-4">
                        @csrf

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nom complet *</label>
                            <input type="text" name="name" required placeholder="Ex : Ouedraogo Moussa" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Adresse email *</label>
                            <input type="email" name="email" required placeholder="moussa@atelier.bf" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Téléphone</label>
                            <input type="text" name="phone" placeholder="+226 70 00 00 00" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Rôle attribué *</label>
                            <select name="role" required class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="technician">Technicien (Interventions, diagnostics, mises à jour)</option>
                                <option value="admin">Administrateur (Gestion complète de l'atelier)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Mot de passe temporaire *</label>
                            <input type="password" name="password" required minlength="8" placeholder="Au moins 8 caractères" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <span class="text-[11px] text-slate-400 mt-1 block">Le collaborateur pourra modifier son mot de passe depuis son profil.</span>
                        </div>

                        <div class="pt-4 flex items-center justify-end space-x-3 border-t border-slate-100">
                            <button type="button" @click="open = false" class="px-4 py-2.5 rounded-xl text-xs font-semibold border border-slate-200 text-slate-600 hover:bg-slate-50">
                                Annuler
                            </button>
                            <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-semibold bg-blue-600 text-white hover:bg-blue-700 shadow-sm">
                                Enregistrer le collaborateur
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
