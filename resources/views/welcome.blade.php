<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-900 text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RepairOS — Plateforme de Gestion des Réparations & Interventions Techniques</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif; }
    </style>
</head>
<body class="min-h-full flex flex-col justify-between antialiased selection:bg-blue-600 selection:text-white">

    <!-- Top Navigation -->
    <header class="w-full max-w-6xl mx-auto px-6 py-6 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-blue-600 text-white font-black text-xl flex items-center justify-center shadow-lg shadow-blue-500/30">
                R
            </div>
            <div>
                <span class="font-bold text-white text-lg tracking-tight">Repair<span class="text-blue-500">OS</span></span>
                <span class="block text-[10px] text-slate-400 font-medium uppercase tracking-wider">SaaS Ateliers Professionnels</span>
            </div>
        </div>

        <div class="flex items-center space-x-4">
            @auth
                <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold bg-blue-600 hover:bg-blue-500 text-white shadow-md transition-all">
                    Accéder à mon Atelier &rarr;
                </a>
            @else
                <a href="{{ route('login') }}" class="text-xs font-semibold text-slate-300 hover:text-white transition-colors">
                    Connexion
                </a>
                <a href="{{ route('register') }}" class="px-4 py-2 rounded-xl text-xs font-semibold bg-blue-600 hover:bg-blue-500 text-white shadow-md shadow-blue-600/20 transition-all">
                    Créer mon atelier
                </a>
            @endauth
        </div>
    </header>

    <!-- Hero Section -->
    <main class="w-full max-w-5xl mx-auto px-6 py-12 md:py-20 text-center space-y-8 my-auto">
        <div class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-semibold bg-blue-950/80 text-blue-400 border border-blue-800/60 shadow-inner">
            <span class="w-2 h-2 rounded-full bg-blue-500 mr-2 animate-ping"></span>
            Version 1.0 Production &bull; Conçu pour le Burkina Faso & l'Afrique Francophone
        </div>

        <h1 class="text-3xl sm:text-5xl md:text-6xl font-extrabold text-white tracking-tight leading-[1.15]">
            Gérez chaque réparation avec <span class="bg-gradient-to-r from-blue-400 via-sky-300 to-indigo-400 bg-clip-text text-transparent">rigueur et professionnalisme</span>.
        </h1>

        <p class="max-w-2xl mx-auto text-sm sm:text-base text-slate-400 leading-relaxed">
            De la prise en charge de l'équipement au diagnostic, du devis à la facture, suivez l'ensemble du cycle de vie de votre atelier en FCFA (XOF).
        </p>

        <!-- CTA Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
            <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-xl text-sm font-bold bg-blue-600 hover:bg-blue-500 text-white shadow-xl shadow-blue-600/30 hover:scale-[1.02] transition-all">
                Démarrer mon atelier gratuit
            </a>
            <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-xl text-sm font-bold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition-all">
                Se connecter
            </a>
        </div>

        <!-- Client Tracking Fast Lookup -->
        <div class="pt-8 max-w-md mx-auto">
            <div class="p-4 rounded-2xl bg-slate-800/60 border border-slate-700/80 backdrop-blur-sm text-left">
                <span class="text-xs font-bold text-slate-300 uppercase tracking-wider block mb-2">Vous êtes client ? Suivre votre appareil</span>
                <form onsubmit="event.preventDefault(); const t = document.getElementById('tokenInput').value.trim(); if(t) window.location.href = '/suivi/' + encodeURIComponent(t);" class="flex gap-2">
                    <input type="text" id="tokenInput" placeholder="Collez votre code de suivi..." required class="flex-1 px-3.5 py-2 text-xs rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-700 hover:bg-slate-600 text-white transition-colors">
                        Suivre
                    </button>
                </form>
            </div>
        </div>

        <!-- Feature Pills -->
        <div class="pt-10 grid grid-cols-2 md:grid-cols-4 gap-4 text-left">
            <div class="p-4 rounded-xl bg-slate-800/40 border border-slate-800">
                <div class="text-blue-400 font-bold text-sm">Prise en charge</div>
                <div class="text-xs text-slate-400 mt-1">Bon de dépôt PDF, photos des avaries et traçabilité de l'état entrant.</div>
            </div>
            <div class="p-4 rounded-xl bg-slate-800/40 border border-slate-800">
                <div class="text-sky-400 font-bold text-sm">Devis & Diagnostic</div>
                <div class="text-xs text-slate-400 mt-1">Calcul des prestations et pièces au centime près en devises locales.</div>
            </div>
            <div class="p-4 rounded-xl bg-slate-800/40 border border-slate-800">
                <div class="text-emerald-400 font-bold text-sm">Factures & Reçus</div>
                <div class="text-xs text-slate-400 mt-1">Gestion des acomptes, solde dû, Mobile Money et espèces.</div>
            </div>
            <div class="p-4 rounded-xl bg-slate-800/40 border border-slate-800">
                <div class="text-purple-400 font-bold text-sm">Suivi Client Sécurisé</div>
                <div class="text-xs text-slate-400 mt-1">Lien de suivi public par jeton chiffré sans divulgation de secrets.</div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full max-w-6xl mx-auto px-6 py-8 border-t border-slate-800/80 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
        <div>
            &copy; {{ date('Y') }} RepairOS. Tous droits réservés.
        </div>
        <div class="flex items-center space-x-4">
            <span>Ouagadougou &bull; Fuseau Africa/Ouagadougou</span>
            <span>Devise XOF (FCFA)</span>
        </div>
    </footer>
</body>
</html>
