<x-public-layout :title="'Suivi de réparation ' . $ticket->reference">
    <div class="space-y-6">

        <!-- Atelier Card -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-blue-600">Atelier en charge</span>
                <h1 class="text-xl font-bold text-slate-900 mt-0.5">{{ $ticket->tenant->name }}</h1>
                <p class="text-xs text-slate-500 mt-1">
                    {{ $ticket->tenant->address ?: 'Service technique professionnel' }} &bull; {{ $ticket->tenant->city }} ({{ $ticket->tenant->country_code }})
                </p>
            </div>
            @if($ticket->tenant->phone)
                <div class="flex items-center space-x-2">
                    <a href="tel:{{ $ticket->tenant->phone }}" class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-xs font-semibold bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors">
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        Appeler l'atelier
                    </a>
                </div>
            @endif
        </div>

        <!-- Status Hero Box -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-6 border-b border-slate-100 gap-4">
                <div>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Référence du ticket</span>
                    <div class="text-2xl font-extrabold text-slate-900 font-mono tracking-tight">{{ $ticket->reference }}</div>
                </div>

                <div>
                    @php
                        $status = $ticket->status;
                        $colorClasses = match($status->value) {
                            'RECU' => 'bg-slate-100 text-slate-800 border-slate-300',
                            'DIAGNOSTIC' => 'bg-amber-50 text-amber-800 border-amber-200',
                            'DEVIS_EN_ATTENTE', 'EN_ATTENTE_CLIENT' => 'bg-sky-50 text-sky-800 border-sky-200',
                            'EN_REPARATION' => 'bg-blue-50 text-blue-800 border-blue-200',
                            'TEST' => 'bg-indigo-50 text-indigo-800 border-indigo-200',
                            'PRET_A_RETIRER' => 'bg-emerald-100 text-emerald-900 border-emerald-300 ring-2 ring-emerald-500/20',
                            'RESTITUE' => 'bg-slate-100 text-slate-700 border-slate-200',
                            'ANNULE' => 'bg-rose-50 text-rose-800 border-rose-200',
                            default => 'bg-slate-100 text-slate-800',
                        };
                    @endphp
                    <span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-bold border {{ $colorClasses }}">
                        {{ $status->label() }}
                    </span>
                </div>
            </div>

            <!-- Stepper Progress Bar -->
            <div class="mt-6">
                <div class="flex justify-between text-[11px] font-semibold text-slate-500 mb-2">
                    <span>Avancement des travaux</span>
                    <span class="text-blue-600 font-bold">{{ $progressPercent }}%</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                    <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-500" style="width: {{ $progressPercent }}%"></div>
                </div>

                <!-- Steps labels -->
                <div class="grid grid-cols-4 text-center mt-3 text-[10px] sm:text-xs font-medium text-slate-500 gap-1">
                    <div class="{{ in_array($ticket->status->value, ['RECU', 'DIAGNOSTIC', 'DEVIS_EN_ATTENTE', 'EN_REPARATION', 'TEST', 'PRET_A_RETIRER', 'RESTITUE']) ? 'text-blue-600 font-bold' : '' }}">
                        1. Dépôt
                    </div>
                    <div class="{{ in_array($ticket->status->value, ['DIAGNOSTIC', 'DEVIS_EN_ATTENTE', 'EN_REPARATION', 'TEST', 'PRET_A_RETIRER', 'RESTITUE']) ? 'text-blue-600 font-bold' : '' }}">
                        2. Diagnostic
                    </div>
                    <div class="{{ in_array($ticket->status->value, ['EN_REPARATION', 'TEST', 'PRET_A_RETIRER', 'RESTITUE']) ? 'text-blue-600 font-bold' : '' }}">
                        3. Réparation
                    </div>
                    <div class="{{ in_array($ticket->status->value, ['PRET_A_RETIRER', 'RESTITUE']) ? 'text-emerald-600 font-bold' : '' }}">
                        4. Retrait
                    </div>
                </div>
            </div>

            <!-- Ready for pickup announcement -->
            @if($ticket->status->value === 'PRET_A_RETIRER')
                <div class="mt-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-start space-x-3">
                    <div class="p-2 bg-emerald-500 text-white rounded-lg shrink-0 mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-emerald-950">Votre équipement est prêt à être récupéré !</h4>
                        <p class="text-xs text-emerald-800 mt-1 leading-relaxed">
                            L'intervention et les tests de contrôle sont terminés. Vous pouvez vous présenter à l'atelier muni de votre bon de prise en charge pour récupérer votre appareil.
                        </p>
                    </div>
                </div>
            @elseif($ticket->status->value === 'RESTITUE')
                <div class="mt-6 p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-center space-x-3 text-slate-700 text-xs">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Cet appareil a été restitué le {{ $ticket->closed_at ? $ticket->closed_at->format('d/m/Y à H:i') : 'Récemment' }}. Merci pour votre confiance !</span>
                </div>
            @endif
        </div>

        <!-- Device & Problem Details -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Équipement en charge</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-100">
                    <span class="text-slate-400 block text-[11px]">Appareil</span>
                    <span class="font-bold text-slate-900 text-sm mt-0.5 block">{{ $ticket->device->brand }} {{ $ticket->device->model }}</span>
                    <span class="text-slate-500 text-[11px]">{{ $ticket->device->device_category_label }}</span>
                </div>

                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-100">
                    <span class="text-slate-400 block text-[11px]">Date de dépôt</span>
                    <span class="font-bold text-slate-900 text-sm mt-0.5 block">{{ $ticket->deposit_date->format('d/m/Y à H:i') }}</span>
                    @if($ticket->estimated_completion_date)
                        <span class="text-blue-600 text-[11px]">Fin estimée : {{ $ticket->estimated_completion_date->format('d/m/Y') }}</span>
                    @else
                        <span class="text-slate-500 text-[11px]">Délai communiqué après diagnostic</span>
                    @endif
                </div>
            </div>

            <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                <span class="text-slate-400 block text-[11px] font-semibold uppercase">Problème signalé au dépôt</span>
                <p class="text-xs text-slate-800 mt-1 leading-relaxed">{{ $ticket->reported_issue }}</p>
            </div>

            @if($ticket->accessories_received)
                <div class="text-xs text-slate-600 bg-slate-50/60 p-3 rounded-lg border border-slate-100">
                    <span class="font-semibold text-slate-700">Accessoires déposés :</span> {{ $ticket->accessories_received }}
                </div>
            @endif
        </div>

        <!-- Notes destinées au client -->
        @if($ticket->client_notes)
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Message de l'atelier</h3>
                <div class="p-4 rounded-xl bg-blue-50/60 border border-blue-100 text-xs text-blue-950 leading-relaxed whitespace-pre-line">
                    {{ $ticket->client_notes }}
                </div>
            </div>
        @endif

        <!-- Client-facing interventions log -->
        @if($ticket->interventions->count() > 0)
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Rapport d'intervention</h3>
                <div class="space-y-3">
                    @foreach($ticket->interventions as $intervention)
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 text-xs space-y-1.5">
                            <div class="flex items-center justify-between text-slate-400 text-[11px]">
                                <span class="font-semibold text-slate-700">{{ $intervention->intervention_type }}</span>
                                <span>{{ $intervention->created_at->format('d/m/Y') }}</span>
                            </div>
                            <p class="text-slate-800 leading-relaxed">{{ $intervention->customer_summary }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Photos et documents publics -->
        @if($ticket->attachments->count() > 0)
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Photos et documents</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach($ticket->attachments as $att)
                        <a href="{{ route('public.tracking.attachment', ['token' => $ticket->public_tracking_token, 'attachment' => $att->id]) }}" target="_blank" class="block p-3 rounded-xl border border-slate-200 hover:border-blue-400 bg-slate-50 text-center transition-all">
                            @if(str_starts_with($att->mime_type, 'image/'))
                                <img src="{{ route('public.tracking.attachment', ['token' => $ticket->public_tracking_token, 'attachment' => $att->id]) }}" alt="Photo" class="w-full h-24 object-cover rounded-lg mb-2">
                            @else
                                <div class="w-full h-24 bg-slate-100 rounded-lg flex items-center justify-center text-slate-400 mb-2">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                            <span class="text-[11px] font-medium text-slate-700 truncate block">{{ $att->original_filename }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Security & Confidentiality disclaimer -->
        <div class="text-center text-[11px] text-slate-400 px-4">
            Ce lien est sécurisé et réservé au suivi de votre réparation. Aucune information confidentielle de tiers n'est exposée.
        </div>
    </div>
</x-public-layout>
