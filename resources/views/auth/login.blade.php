<x-layouts.guest>
    <x-slot:title>Connexion Atelier</x-slot:title>

    <div class="mb-5 text-center">
        <h2 class="text-xl font-bold text-slate-900">Espace Professionnel</h2>
        <p class="text-xs text-slate-500 mt-1">Connectez-vous pour accéder à votre atelier</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Adresse E-mail</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                   placeholder="ex: technicien@atelier.bf"
                   class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
        </div>

        <div>
            <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Mot de passe</label>
            <input id="password" type="password" name="password" required
                   placeholder="••••••••"
                   class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
        </div>

        <div class="flex items-center justify-between text-xs">
            <label class="flex items-center text-slate-600 cursor-pointer">
                <input type="checkbox" name="remember" class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                <span class="ml-2">Se souvenir de moi</span>
            </label>
        </div>

        <button type="submit" class="w-full py-2.5 px-4 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm shadow-sm transition">
            Se connecter
        </button>

        <div class="pt-4 border-t border-slate-200 text-center text-xs text-slate-600">
            Vous n'avez pas encore d'atelier ?
            <a href="{{ route('register') }}" class="font-semibold text-blue-600 hover:text-blue-700 underline">Créer votre atelier</a>
        </div>
    </form>
</x-layouts.guest>
