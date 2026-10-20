<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Tableau de bord' }} — RepairOS</title>

    <!-- Polices Google Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif; }
    </style>
</head>
<body class="h-full antialiased text-slate-800" x-data="{ mobileMenuOpen: false }">
    @php
        $currentTenant = app(\App\Services\TenantManager::class)->getTenant();
        $currentUser = auth()->user();
        $userRole = $currentUser && $currentTenant ? $currentUser->roleInTenant($currentTenant->id) : null;
    @endphp

    <div class="min-h-full flex flex-col lg:flex-row">
        <!-- Sidebar Desktop -->
        <aside class="hidden lg:flex lg:flex-col lg:w-64 bg-slate-900 text-slate-300 border-r border-slate-800 shrink-0">
            <!-- Logo & Nom de l'atelier -->
            <div class="h-16 flex items-center px-6 bg-slate-950 border-b border-slate-800 justify-between">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold text-lg shadow-sm">
                        R
                    </div>
                    <div>
                        <span class="font-bold text-white text-base tracking-tight">Repair<span class="text-blue-400">OS</span></span>
                        <div class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">Maintenance Pro</div>
                    </div>
                </a>
            </div>

            <!-- Espace actif -->
            @if($currentTenant)
            <div class="px-5 py-3.5 bg-slate-900/60 border-b border-slate-800/80">
                <div class="text-xs text-slate-400">Atelier connecté :</div>
                <div class="font-semibold text-white truncate text-sm mt-0.5">{{ $currentTenant->name }}</div>
                <div class="flex items-center space-x-2 mt-1">
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-900/50 text-blue-300 border border-blue-700/50">
                        {{ $currentTenant->currency }}
                    </span>
                    <span class="text-[11px] text-slate-400">
                        {{ $userRole ? $userRole->label() : '' }}
                    </span>
                </div>
            </div>
            @endif

            <!-- Navigation Principale -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto text-sm font-medium">
                <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2.5 rounded-lg transition {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Tableau de bord
                </a>

                <a href="{{ route('tickets.index') }}" class="flex items-center px-3 py-2.5 rounded-lg transition {{ request()->routeIs('tickets.*') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                    Réparations
                </a>

                <a href="{{ route('customers.index') }}" class="flex items-center px-3 py-2.5 rounded-lg transition {{ request()->routeIs('customers.*') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Clients
                </a>

                <a href="{{ route('devices.index') }}" class="flex items-center px-3 py-2.5 rounded-lg transition {{ request()->routeIs('devices.*') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Appareils
                </a>

                <a href="{{ route('quotes.index') }}" class="flex items-center px-3 py-2.5 rounded-lg transition {{ request()->routeIs('quotes.*') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Devis
                </a>

                <a href="{{ route('invoices.index') }}" class="flex items-center px-3 py-2.5 rounded-lg transition {{ request()->routeIs('invoices.*') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Factures & Paiements
                </a>

                <a href="{{ route('reports.index') }}" class="flex items-center px-3 py-2.5 rounded-lg transition {{ request()->routeIs('reports.*') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Rapports & Stats
                </a>

                @if($currentUser && $currentTenant && $currentUser->isOwnerOf($currentTenant->id))
                <div class="pt-4 pb-1 px-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                    Gestion Atelier
                </div>

                <a href="{{ route('team.index') }}" class="flex items-center px-3 py-2.5 rounded-lg transition {{ request()->routeIs('team.*') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Équipe & Techniciens
                </a>

                <a href="{{ route('settings.index') }}" class="flex items-center px-3 py-2.5 rounded-lg transition {{ request()->routeIs('settings.*') ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Paramètres
                </a>
                @endif
            </nav>

            <!-- Profil utilisateur footer -->
            <div class="p-4 border-t border-slate-800 bg-slate-950 flex items-center justify-between">
                <div class="flex items-center min-w-0 mr-2">
                    <div class="w-8 h-8 rounded-full bg-slate-700 text-white flex items-center justify-center font-bold text-xs uppercase shrink-0">
                        {{ substr($currentUser->name ?? 'U', 0, 2) }}
                    </div>
                    <div class="ml-2.5 truncate">
                        <div class="text-xs font-semibold text-white truncate">{{ $currentUser->name ?? '' }}</div>
                        <div class="text-[11px] text-slate-400 truncate">{{ $currentUser->email ?? '' }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Se déconnecter" class="text-slate-400 hover:text-white p-1.5 rounded-lg hover:bg-slate-800 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Contenu Principal -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Topbar Mobile & Recherche -->
            <header class="h-16 bg-white border-b border-slate-200 px-4 sm:px-6 flex items-center justify-between shrink-0 shadow-xs">
                <!-- Hamburger Mobile -->
                <div class="flex items-center lg:hidden">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="text-slate-600 hover:text-slate-900 p-2 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <span class="ml-2 font-bold text-slate-900">Repair<span class="text-blue-600">OS</span></span>
                </div>

                <!-- Recherche rapide de ticket -->
                <div class="hidden sm:flex items-center flex-1 max-w-md">
                    <form action="{{ route('tickets.index') }}" method="GET" class="w-full relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" name="search" placeholder="Recherche rapide (Réf ticket, client, téléphone, n° série)..." value="{{ request('search') }}"
                               class="w-full pl-9 pr-4 py-1.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                    </form>
                </div>

                <!-- Actions Droite -->
                <div class="flex items-center space-x-3">
                    <a href="{{ route('tickets.create') }}" class="inline-flex items-center px-3.5 py-1.5 rounded-lg text-xs sm:text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Nouveau Ticket
                    </a>
                </div>
            </header>

            <!-- Menu mobile déroulant -->
            <div x-show="mobileMenuOpen" class="lg:hidden bg-slate-900 text-white border-b border-slate-800 px-4 py-3 space-y-1">
                <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-md text-sm font-medium hover:bg-slate-800">Tableau de bord</a>
                <a href="{{ route('tickets.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium hover:bg-slate-800">Réparations</a>
                <a href="{{ route('customers.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium hover:bg-slate-800">Clients</a>
                <a href="{{ route('devices.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium hover:bg-slate-800">Appareils</a>
                <a href="{{ route('quotes.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium hover:bg-slate-800">Devis</a>
                <a href="{{ route('invoices.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium hover:bg-slate-800">Factures & Paiements</a>
                <a href="{{ route('reports.index') }}" class="block px-3 py-2 rounded-md text-sm font-medium hover:bg-slate-800">Rapports</a>
            </div>

            <!-- Messages flash Toast / Alerts -->
            <div class="px-4 sm:px-6 pt-4">
                @if(session('success'))
                <div class="flex items-center p-3.5 mb-2 text-sm text-emerald-800 rounded-lg bg-emerald-50 border border-emerald-200" role="alert">
                    <svg class="w-5 h-5 mr-2 shrink-0 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
                @endif

                @if(session('error'))
                <div class="flex items-center p-3.5 mb-2 text-sm text-rose-800 rounded-lg bg-rose-50 border border-rose-200" role="alert">
                    <svg class="w-5 h-5 mr-2 shrink-0 text-rose-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
                @endif

                @if($errors->any())
                <div class="p-3.5 mb-2 text-sm text-rose-800 rounded-lg bg-rose-50 border border-rose-200" role="alert">
                    <div class="font-bold mb-1">Veuillez corriger les erreurs suivantes :</div>
                    <ul class="list-disc list-inside space-y-0.5 text-xs">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>

            <!-- Page Body -->
            <main class="flex-1 overflow-y-auto px-4 sm:px-6 py-4">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
