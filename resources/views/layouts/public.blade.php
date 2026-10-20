<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title ?? 'Suivi de réparation' }} — RepairOS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif; }
    </style>
</head>
<body class="min-h-full antialiased text-slate-800 flex flex-col justify-between py-6 px-4 sm:px-6">
    <div class="w-full max-w-2xl mx-auto">
        <!-- Header -->
        <header class="flex items-center justify-between pb-6 border-b border-slate-200 mb-6">
            <div class="flex items-center space-x-2.5">
                <div class="w-9 h-9 rounded-xl bg-blue-600 text-white font-bold text-lg flex items-center justify-center shadow-sm">
                    R
                </div>
                <div>
                    <span class="font-bold text-slate-900 text-base tracking-tight">Repair<span class="text-blue-600">OS</span></span>
                    <div class="text-[11px] text-slate-500 font-medium">Espace Suivi Réparation Client</div>
                </div>
            </div>
            <div class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                <span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                Suivi Sécurisé en Direct
            </div>
        </header>

        <main>
            {{ $slot }}
        </main>
    </div>

    <!-- Footer -->
    <footer class="w-full max-w-2xl mx-auto text-center pt-8 text-xs text-slate-400">
        Propulsé par <strong>RepairOS</strong> — Plateforme professionnelle de suivi des interventions techniques
    </footer>
</body>
</html>
