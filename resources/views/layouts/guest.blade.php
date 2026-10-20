<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Connexion' }} — RepairOS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif; }
    </style>
</head>
<body class="h-full antialiased text-slate-800 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Brand Header -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-blue-600 text-white font-extrabold text-2xl shadow-md mb-2">
                R
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Repair<span class="text-blue-600">OS</span></h1>
            <p class="text-xs text-slate-500 mt-0.5">SaaS de gestion d'atelier & suivi de réparations</p>
        </div>

        @if(session('error'))
        <div class="p-3.5 mb-4 text-sm text-rose-800 rounded-lg bg-rose-50 border border-rose-200">
            {{ session('error') }}
        </div>
        @endif

        @if(session('success'))
        <div class="p-3.5 mb-4 text-sm text-emerald-800 rounded-lg bg-emerald-50 border border-emerald-200">
            {{ session('success') }}
        </div>
        @endif

        <!-- Card Container -->
        <div class="bg-white py-6 px-6 sm:px-8 shadow-sm border border-slate-200 rounded-2xl">
            {{ $slot }}
        </div>

        <div class="text-center mt-6 text-xs text-slate-400">
            RepairOS &copy; {{ date('Y') }} — Burkina Faso & Afrique Francophone
        </div>
    </div>
</body>
</html>
