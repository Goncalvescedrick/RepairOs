<x-layouts.app>
    <x-slot:title>Stock & Pièces Détachées</x-slot:title>

    <div class="space-y-6" x-data="{ createModalOpen: false, adjustModalOpen: false, selectedPart: null }">
        <!-- En-tête -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Catalogue & Stock de Pièces Détachées</h1>
                <p class="text-sm text-slate-500 mt-0.5">Gérez vos composants en stock, suivez les seuils critiques et calculez automatiquement vos marges</p>
            </div>
            @can('create', \App\Models\SparePart::class)
            <button type="button" @click="createModalOpen = true" class="inline-flex items-center px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-semibold shadow-sm transition">
                + Ajouter une Pièce au Stock
            </button>
            @endcan
        </div>

        <!-- KPIs Stock -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Références Catalogue</span>
                <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ $parts->total() }}</div>
                <span class="text-[11px] text-slate-500">Pièces et composants référencés</span>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Valeur d'Achat du Stock</span>
                <div class="text-2xl font-extrabold text-blue-700 mt-1">{{ number_format($totalStockValue, 0, ',', ' ') }} {{ $tenant->currency }}</div>
                <span class="text-[11px] text-slate-500">Immobilisation valorisée au coût d'achat</span>
            </div>

            <a href="{{ route('parts.index', ['low_stock' => 1]) }}" class="bg-white p-4 rounded-xl border {{ $lowStockCount > 0 ? 'border-rose-300 bg-rose-50/30' : 'border-slate-200' }} shadow-xs block">
                <span class="text-xs font-semibold uppercase tracking-wider {{ $lowStockCount > 0 ? 'text-rose-700' : 'text-slate-400' }}">Alertes Rupture / Seuil Bas</span>
                <div class="text-2xl font-extrabold {{ $lowStockCount > 0 ? 'text-rose-600' : 'text-emerald-600' }} mt-1">{{ $lowStockCount }}</div>
                <span class="text-[11px] text-slate-500">Cliquez pour filtrer les pièces à réapprovisionner</span>
            </a>
        </div>

        <!-- Filtres -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <form method="GET" action="{{ route('parts.index') }}" class="flex flex-col sm:flex-row gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par désignation, SKU ou compatibilité..."
                       class="flex-1 px-3.5 py-2 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="category" class="px-3 py-2 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl">
                    <option value="">Toutes catégories</option>
                    @foreach(['ECRAN' => 'Écran / Dalle', 'BATTERIE' => 'Batterie', 'STOCKAGE' => 'SSD / Disque', 'MEMOIRE' => 'Mémoire RAM', 'CONNECTEUR' => 'Connecteur / Nappe', 'CARTE_MERE' => 'Composant Carte Mère', 'CHARGEUR' => 'Chargeur / Alimentation', 'AUTRE' => 'Autre'] as $key => $label)
                        <option value="{{ $key }}" {{ request('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 bg-slate-900 text-white text-xs font-semibold rounded-xl hover:bg-slate-800 transition">
                    Filtrer
                </button>
            </form>
        </div>

        <!-- Tableau des Pièces -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            @if($parts->isEmpty())
                <div class="p-10 text-center text-slate-400 space-y-2">
                    <p class="text-sm font-medium text-slate-600">Aucune pièce détachée enregistrée dans votre stock.</p>
                    <p class="text-xs">Ajoutez vos écrans, SSD, batteries et composants pour les sélectionner directement dans vos devis et factures.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="px-5 py-3">Désignation & SKU</th>
                                <th class="px-4 py-3">Catégorie</th>
                                <th class="px-4 py-3 text-right">Prix Achat</th>
                                <th class="px-4 py-3 text-right">Prix Vente</th>
                                <th class="px-4 py-3 text-right">Marge Unit.</th>
                                <th class="px-4 py-3 text-center">En Stock</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($parts as $part)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="px-5 py-3">
                                        <div class="font-bold text-slate-900">{{ $part->name }}</div>
                                        <div class="text-[11px] text-slate-400">
                                            {{ $part->sku ? "SKU: {$part->sku}" : 'Sans SKU' }}
                                            @if($part->brand_compatibility)
                                                &bull; {{ $part->brand_compatibility }}
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold text-[10px]">
                                            {{ $part->category }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right text-slate-600">
                                        {{ number_format($part->purchase_price, 0, ',', ' ') }} {{ $tenant->currency }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-slate-900">
                                        {{ number_format($part->selling_price, 0, ',', ' ') }} {{ $tenant->currency }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-emerald-700">
                                        +{{ number_format($part->unitMargin(), 0, ',', ' ') }} {{ $tenant->currency }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $part->isLowStock() ? 'bg-rose-100 text-rose-800 border border-rose-300' : 'bg-emerald-50 text-emerald-800 border border-emerald-200' }}">
                                            {{ $part->stock_quantity }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right space-x-1 whitespace-nowrap">
                                        @can('update', $part)
                                            <form action="{{ route('parts.adjust-stock', $part) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="delta" value="1">
                                                <button type="submit" class="px-2 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded font-bold text-[11px]" title="Ajouter 1 au stock">+1</button>
                                            </form>
                                            <form action="{{ route('parts.adjust-stock', $part) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="delta" value="-1">
                                                <button type="submit" class="px-2 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 rounded font-bold text-[11px]" title="Retirer 1 du stock">-1</button>
                                            </form>
                                        @endcan
                                        @can('delete', $part)
                                            <form action="{{ route('parts.destroy', $part) }}" method="POST" class="inline" onsubmit="return confirm('Archiver cette référence de pièce ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2 py-1 text-rose-600 hover:underline text-[11px]">Archiver</button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-5 py-3 border-t border-slate-100">
                    {{ $parts->links() }}
                </div>
            @endif
        </div>

        <!-- Modale Création Pièce Détachée -->
        <div x-show="createModalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-200 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                    <h3 class="text-base font-bold text-slate-900">Nouvelle Pièce Détachée</h3>
                    <button type="button" @click="createModalOpen = false" class="text-slate-400 hover:text-slate-600">&times;</button>
                </div>

                <form action="{{ route('parts.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Désignation de la pièce *</label>
                        <input type="text" name="name" required placeholder="ex: Dalle Écran 14 pouces FHD IPS 30 pins" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Catégorie *</label>
                            <select name="category" required class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg">
                                <option value="ECRAN">Écran / Dalle</option>
                                <option value="BATTERIE">Batterie</option>
                                <option value="STOCKAGE">SSD / Disque</option>
                                <option value="MEMOIRE">Mémoire RAM</option>
                                <option value="CONNECTEUR">Connecteur / Nappe</option>
                                <option value="CARTE_MERE">Composant Carte Mère</option>
                                <option value="CHARGEUR">Chargeur / Alimentation</option>
                                <option value="AUTRE">Autre</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Référence / SKU</label>
                            <input type="text" name="sku" placeholder="ex: EDP-14-30P" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Modèles compatibles</label>
                        <input type="text" name="brand_compatibility" placeholder="ex: Dell Latitude 5420 / HP EliteBook 840" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Prix d'achat unitaire ({{ $tenant->currency }}) *</label>
                            <input type="number" step="100" min="0" name="purchase_price" value="0" required class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Prix de vente conseillé ({{ $tenant->currency }}) *</label>
                            <input type="number" step="100" min="0" name="selling_price" value="0" required class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Quantité initiale en stock *</label>
                            <input type="number" min="0" name="stock_quantity" value="5" required class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Seuil d'alerte stock bas *</label>
                            <input type="number" min="0" name="min_stock_alert" value="2" required class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg">
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="createModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800">
                            Annuler
                        </button>
                        <button type="submit" class="px-4 py-2 text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                            Enregistrer la pièce
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
