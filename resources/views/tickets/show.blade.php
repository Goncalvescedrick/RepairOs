<x-layouts.app>
    <x-slot:title>Ticket {{ $ticket->reference }}</x-slot:title>

    <div class="space-y-6" x-data="{
        cancelModalOpen: false,
        paymentModalOpen: false,
        interventionModalOpen: false,
        showAccessCode: false,
        aiGenerating: false,
        copySuccess: false,
        copyTrackingLink() {
            navigator.clipboard.writeText('{{ url("/suivi/{$ticket->public_tracking_token}") }}');
            this.copySuccess = true;
            setTimeout(() => this.copySuccess = false, 2500);
        },
        async generateAiClientSummary() {
            this.aiGenerating = true;
            try {
                const res = await fetch('{{ route("tickets.interventions.ai-summary", $ticket) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        intervention_type: document.getElementById('intervention_type').value,
                        work_performed: document.getElementById('work_performed').value,
                        diagnosis: document.getElementById('diagnosis').value,
                        test_results: document.getElementById('test_results').value,
                    })
                });
                const data = await res.json();
                if (data.summary) {
                    document.getElementById('customer_summary').value = data.summary;
                }
            } finally {
                this.aiGenerating = false;
            }
        }
    }">
        <!-- En-tête du Ticket -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center space-x-3">
                    <span class="font-mono text-2xl font-extrabold text-slate-900 tracking-tight">{{ $ticket->reference }}</span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {{ $ticket->status->badgeClasses() }}">
                        {{ $ticket->status->label() }}
                    </span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold border {{ $ticket->priority->badgeClasses() }}">
                        Priorité {{ $ticket->priority->label() }}
                    </span>
                    @if($ticket->isUnderWarranty())
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                        Garantie SAV jusqu'au {{ $ticket->warranty_expires_at->format('d/m/Y') }}
                    </span>
                    @endif
                </div>
                <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 mt-1.5">
                    <span>Dépôt le <strong>{{ $ticket->deposit_date->format('d/m/Y à H:i') }}</strong></span>
                    <span>&bull;</span>
                    @if($ticket->estimated_completion_date)
                    <span>Fin estimée le <strong class="{{ $ticket->estimated_completion_date->isPast() && !$ticket->status->isTerminal() ? 'text-rose-600 font-bold' : '' }}">{{ $ticket->estimated_completion_date->format('d/m/Y') }}</strong></span>
                    <span>&bull;</span>
                    @endif
                    <span>Technicien : <strong>{{ $ticket->assignedTo?->name ?: 'Non assigné' }}</strong></span>
                </div>
            </div>

            <!-- Actions Rapides & Transitions de Statuts -->
            <div class="flex flex-wrap items-center gap-2">
                <!-- Lien WhatsApp client -->
                <a href="{{ $intakeWhatsAppUrl }}" target="_blank" class="inline-flex items-center px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-600 hover:text-white text-xs font-semibold transition" title="Informer le client sur WhatsApp">
                    <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                    WhatsApp Client
                </a>

                <!-- Bon de dépôt imprimable -->
                <a href="{{ route('tickets.receipt', $ticket) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs font-semibold transition">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Bon de Dépôt
                </a>

                <!-- Étiquette Thermique QR -->
                <a href="{{ route('tickets.label', $ticket) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 rounded-xl bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100 text-xs font-semibold transition" title="Imprimer l'étiquette autocollante avec QR Code">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    Étiquette QR
                </a>

                <!-- Boutons de transitions permises -->
                @foreach($allowedTransitions as $targetStatus)
                    @if($targetStatus === \App\Enums\RepairStatus::ANNULE)
                    <button type="button" @click="cancelModalOpen = true" class="px-3 py-1.5 rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-semibold transition border border-rose-200">
                        Annuler...
                    </button>
                    @else
                    <form action="{{ route('tickets.transition', $ticket) }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="status" value="{{ $targetStatus->value }}">
                        <button type="submit" class="px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition shadow-xs">
                            Passer à : {{ $targetStatus->label() }}
                        </button>
                    </form>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- Suivi Public URL Banner -->
        <div class="bg-blue-50/70 p-3.5 rounded-xl border border-blue-100 flex flex-col sm:flex-row sm:items-center justify-between text-xs gap-2">
            <div class="flex items-center text-blue-900">
                <svg class="w-4 h-4 mr-2 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                <span>Lien de suivi public client : <code class="bg-white px-2 py-0.5 rounded border border-blue-200 font-mono select-all">{{ url("/suivi/{$ticket->public_tracking_token}") }}</code></span>
            </div>
            <button @click="copyTrackingLink()" type="button" class="px-3 py-1 bg-white hover:bg-blue-100 text-blue-700 font-medium rounded-lg border border-blue-200 transition shrink-0">
                <span x-show="!copySuccess">Copier le lien</span>
                <span x-show="copySuccess" class="text-emerald-600 font-bold">Lien copié !</span>
            </button>
        </div>

        <!-- Contenu en 2 Colonnes -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Colonne Gauche : Diagnostic, Interventions, Travaux (2 cols) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Panne déclarée & constatation d'entrée -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-2.5">
                        <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Constat Initial à la Réception</h2>
                        <a href="{{ route('tickets.edit', $ticket) }}" class="text-xs font-semibold text-blue-600 hover:underline">
                            Modifier
                        </a>
                    </div>
                    <div class="text-sm">
                        <div class="font-semibold text-slate-900">Problème déclaré par le client :</div>
                        <p class="text-slate-700 mt-1 whitespace-pre-line bg-slate-50 p-3 rounded-xl border border-slate-100">{{ $ticket->reported_issue }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs pt-2">
                        <div>
                            <span class="text-slate-400 font-medium block">État constaté :</span>
                            <span class="text-slate-700 font-medium">{{ $ticket->intake_condition ?: 'Non consigné' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 font-medium block">Accessoires déposés :</span>
                            <span class="text-slate-700 font-medium">{{ $ticket->accessories_received ?: 'Aucun accessoire' }}</span>
                        </div>
                    </div>

                    @if(!empty($ticket->intake_checklist))
                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-slate-400 font-semibold block text-[11px] uppercase tracking-wider mb-1.5">Grille d'inspection visuelle à l'entrée :</span>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs">
                            @php
                                $checkLabels = [
                                    'screen' => 'Écran',
                                    'keyboard' => 'Clavier',
                                    'chassis' => 'Châssis',
                                    'battery' => 'Batterie',
                                    'ports' => 'Connectique',
                                    'power' => 'Allumage',
                                ];
                            @endphp
                            @foreach($ticket->intake_checklist as $key => $val)
                                @if($val)
                                <div class="bg-slate-50 px-2.5 py-1.5 rounded-lg border border-slate-200/80 flex items-center justify-between">
                                    <span class="text-slate-500">{{ $checkLabels[$key] ?? $key }} :</span>
                                    <span class="font-semibold text-slate-800">{{ $val }}</span>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($ticket->decrypted_access_code)
                    <div class="pt-2 text-xs flex items-center justify-between bg-indigo-50/70 p-2.5 rounded-lg border border-indigo-200">
                        <div>
                            <span class="text-indigo-900 font-bold uppercase tracking-wider text-[10px] block">Coffre-fort temporaire (Code PIN / Session) :</span>
                            <span x-show="!showAccessCode" class="font-mono text-indigo-700">••••••••</span>
                            <span x-show="showAccessCode" class="font-mono font-bold text-indigo-950">{{ $ticket->decrypted_access_code }}</span>
                        </div>
                        <button type="button" @click="showAccessCode = !showAccessCode" class="px-2.5 py-1 bg-white text-indigo-700 border border-indigo-200 rounded font-semibold text-[11px]">
                            <span x-text="showAccessCode ? 'Masquer' : 'Révéler'"></span>
                        </button>
                    </div>
                    @endif

                    @if($ticket->internal_notes)
                    <div class="pt-2 text-xs">
                        <span class="text-amber-800 font-bold block uppercase tracking-wider text-[10px]">Note interne confidentielle atelier :</span>
                        <p class="text-amber-900 bg-amber-50/70 p-2.5 rounded-lg border border-amber-200 mt-0.5">{{ $ticket->internal_notes }}</p>
                    </div>
                    @endif
                </div>

                <!-- Interventions Techniques & Diagnostics -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Interventions & Diagnostics Techniques</h2>
                            <p class="text-xs text-slate-500">Traçabilité des examens, pannes résolues et tests effectués</p>
                        </div>
                        <button type="button" @click="interventionModalOpen = true" class="inline-flex items-center px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition">
                            + Ajouter une intervention
                        </button>
                    </div>

                    @if($ticket->interventions->isEmpty())
                    <div class="p-8 text-center text-slate-400">
                        <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <p class="text-sm font-medium text-slate-600">Aucune intervention technique consigée pour l'instant.</p>
                        <button type="button" @click="interventionModalOpen = true" class="mt-2 text-xs font-semibold text-blue-600 hover:underline">
                            Enregistrer le premier diagnostic
                        </button>
                    </div>
                    @else
                    <div class="divide-y divide-slate-100">
                        @foreach($ticket->interventions as $intervention)
                        <div class="p-5 space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center space-x-2">
                                    <span class="font-bold px-2 py-0.5 rounded bg-blue-100 text-blue-800 text-[11px]">
                                        {{ $intervention->intervention_type }}
                                    </span>
                                    <span class="text-slate-500 font-medium">par {{ $intervention->user->name }}</span>
                                    @if($intervention->duration_minutes)
                                    <span class="text-slate-400">&bull; {{ $intervention->duration_minutes }} min</span>
                                    @endif
                                </div>
                                <time class="text-slate-400">{{ $intervention->created_at->format('d/m/Y H:i') }}</time>
                            </div>

                            <div class="text-sm font-semibold text-slate-900 mt-1">
                                {{ $intervention->work_performed }}
                            </div>

                            @if($intervention->diagnosis)
                            <div class="text-xs text-slate-700 bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                                <strong>Diagnostic :</strong> {{ $intervention->diagnosis }}
                                @if($intervention->identified_cause)
                                <div class="text-slate-500 mt-0.5">Cause : {{ $intervention->identified_cause }}</div>
                                @endif
                            </div>
                            @endif

                            @if($intervention->test_results)
                            <div class="text-xs text-emerald-800 bg-emerald-50/70 p-2 rounded-lg border border-emerald-100">
                                <strong>Résultats des tests :</strong> {{ $intervention->test_results }}
                            </div>
                            @endif

                            @if($intervention->customer_summary)
                            <div class="text-xs text-blue-800 bg-blue-50/70 p-2 rounded-lg border border-blue-100">
                                <strong>Résumé pour le client :</strong> {{ $intervention->customer_summary }}
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                <!-- Pièces Jointes & Photos du Ticket -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-2.5">
                        <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Photos & Documents Joints ({{ $ticket->attachments->count() }})</h2>
                    </div>

                    <form action="{{ route('attachments.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row sm:items-center gap-2">
                        @csrf
                        <input type="hidden" name="attachable_type" value="ticket">
                        <input type="hidden" name="attachable_id" value="{{ $ticket->id }}">
                        <select name="file_category" class="px-3 py-1.5 text-xs bg-slate-50 border border-slate-300 rounded-lg">
                            <option value="INTAKE_PHOTO">Photo à l'entrée</option>
                            <option value="DEVICE_PHOTO">Photo de l'appareil</option>
                            <option value="DIAGNOSTIC_DOCUMENT">Rapport diagnostic</option>
                            <option value="PROOF">Preuve / Restitution</option>
                        </select>
                        <input type="file" name="file" accept="image/jpeg,image/png,image/webp,application/pdf" required class="text-xs flex-1">
                        <label class="inline-flex items-center space-x-1.5 text-xs text-slate-600 shrink-0">
                            <input type="checkbox" name="is_public_to_client" value="1" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <span>Visible client</span>
                        </label>
                        <button type="submit" class="px-4 py-1.5 bg-slate-800 text-white rounded-lg text-xs font-semibold hover:bg-slate-900 transition shrink-0">
                            + Téléverser
                        </button>
                    </form>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-3">
                        @forelse($ticket->attachments as $att)
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs space-y-1">
                            <div class="font-bold text-slate-800 truncate" title="{{ $att->original_filename }}">{{ $att->original_filename }}</div>
                            <div class="text-[10px] text-slate-400">{{ $att->humanFileSize() }} &bull; {{ $att->file_category }}</div>
                            <div class="pt-1 flex items-center justify-between">
                                <a href="{{ route('attachments.show', $att) }}" target="_blank" class="text-blue-600 hover:underline font-semibold text-[11px]">
                                    Télécharger
                                </a>
                                @can('delete', $att)
                                <form action="{{ route('attachments.destroy', $att) }}" method="POST" onsubmit="return confirm('Supprimer ce fichier ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:text-rose-800 text-[10px]">Supprimer</button>
                                </form>
                                @endcan
                            </div>
                        </div>
                        @empty
                        <div class="col-span-3 text-center text-slate-400 text-xs py-3">
                            Aucun document ou photo téléversé pour ce ticket.
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Historique des Statuts (Timeline) -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                    <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-4 border-b border-slate-200 pb-2">
                        Journal des Changements de Statut
                    </h2>
                    <ol class="relative border-l border-slate-200 ml-3 space-y-3">
                        @foreach($ticket->statusHistories as $sh)
                        <li class="ml-4">
                            <div class="absolute w-2.5 h-2.5 bg-blue-600 rounded-full mt-1.5 -left-1.5 border border-white"></div>
                            <div class="flex items-center space-x-2 text-xs">
                                <span class="font-bold text-slate-800">{{ $sh->to_status->label() }}</span>
                                <span class="text-slate-400">&bull; {{ $sh->created_at->format('d/m/Y H:i') }}</span>
                                @if($sh->user)
                                <span class="text-slate-500 font-medium">({{ $sh->user->name }})</span>
                                @endif
                            </div>
                            @if($sh->reason)
                            <div class="text-xs italic text-slate-600 mt-0.5 bg-slate-50 p-1.5 rounded">
                                Motif : {{ $sh->reason }}
                            </div>
                            @endif
                        </li>
                        @endforeach
                    </ol>
                </div>
            </div>

            <!-- Colonne Droite : Client, Appareil, Devis, Factures (1 col) -->
            <div class="space-y-6">
                <!-- Fiche Client & Appareil Rapide -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-3">
                    <div class="border-b border-slate-200 pb-2 flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Client & Appareil</span>
                    </div>

                    <div class="space-y-2 text-xs">
                        <div>
                            <span class="text-slate-400 block font-medium">Client :</span>
                            <a href="{{ route('customers.show', $ticket->customer) }}" class="font-bold text-slate-900 text-sm hover:underline block">
                                {{ $ticket->customer->name }}
                            </a>
                            <span class="text-slate-600">{{ $ticket->customer->phone }}</span>
                        </div>
                        <div class="pt-2 border-t border-slate-100">
                            <span class="text-slate-400 block font-medium">Équipement :</span>
                            <a href="{{ route('devices.show', $ticket->device) }}" class="font-bold text-slate-900 text-sm hover:underline block">
                                {{ $ticket->device->brand }} {{ $ticket->device->model }}
                            </a>
                            <span class="text-slate-500">{{ $ticket->device->category_label }} (S/N: {{ $ticket->device->serial_number ?: 'N/A' }})</span>
                        </div>
                    </div>
                </div>

                <!-- Devis associés -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                        <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Devis ({{ $ticket->quotes->count() }})</h2>
                        <a href="{{ route('quotes.create', ['ticket_id' => $ticket->id]) }}" class="text-xs font-semibold text-blue-600 hover:underline">
                            + Nouveau Devis
                        </a>
                    </div>

                    @forelse($ticket->quotes as $quote)
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs space-y-1.5">
                        <div class="flex items-center justify-between">
                            <a href="{{ route('quotes.show', $quote) }}" class="font-mono font-bold text-blue-600 hover:underline">
                                {{ $quote->reference }}
                            </a>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $quote->status->badgeClasses() }}">
                                {{ $quote->status->label() }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between font-bold text-slate-900">
                            <span>Total :</span>
                            <span>{{ number_format($quote->total_amount, 0, ',', ' ') }} {{ $quote->currency }}</span>
                        </div>
                        <div class="pt-1 flex items-center justify-between text-[11px]">
                            <a href="{{ route('quotes.show', $quote) }}" class="text-slate-700 hover:underline">Consulter</a>
                            @if($quote->status === \App\Enums\QuoteStatus::BROUILLON)
                            <form action="{{ route('quotes.send', $quote) }}" method="POST">
                                @csrf
                                <button type="submit" class="text-blue-600 font-semibold hover:underline">Marquer Envoyé</button>
                            </form>
                            @elseif($quote->status === \App\Enums\QuoteStatus::ENVOYE)
                            <form action="{{ route('quotes.accept', $quote) }}" method="POST">
                                @csrf
                                <button type="submit" class="text-emerald-700 font-bold hover:underline">Valider Accord</button>
                            </form>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-slate-400 text-xs py-2">
                        Aucun devis créé pour ce ticket.
                    </div>
                    @endforelse
                </div>

                <!-- Factures & Règlements associés -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                        <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Facturation & Encaissements</h2>
                        <a href="{{ route('invoices.create', ['ticket_id' => $ticket->id]) }}" class="text-xs font-semibold text-blue-600 hover:underline">
                            + Émettre Facture
                        </a>
                    </div>

                    @forelse($ticket->invoices as $invoice)
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs space-y-2">
                        <div class="flex items-center justify-between">
                            <a href="{{ route('invoices.show', $invoice) }}" class="font-mono font-bold text-blue-600 hover:underline">
                                {{ $invoice->reference }}
                            </a>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $invoice->status->badgeClasses() }}">
                                {{ $invoice->status->label() }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-1 text-[11px]">
                            <div>Total : <strong>{{ number_format($invoice->total_amount, 0, ',', ' ') }} {{ $invoice->currency }}</strong></div>
                            <div>Réglé : <strong class="text-emerald-700">{{ number_format($invoice->paid_amount, 0, ',', ' ') }}</strong></div>
                            <div class="col-span-2 text-amber-800">
                                Solde dû : <strong>{{ number_format($invoice->balance_due, 0, ',', ' ') }} {{ $invoice->currency }}</strong>
                            </div>
                        </div>

                        @if((float) $invoice->balance_due > 0 && $invoice->status !== \App\Enums\InvoiceStatus::ANNULEE)
                        <div class="pt-1 border-t border-slate-200 flex justify-end">
                            <a href="{{ route('invoices.show', $invoice) }}#reglement" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded font-semibold text-[11px] transition">
                                Encaisser un règlement
                            </a>
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="text-center text-slate-400 text-xs py-2">
                        Aucune facture émise pour cette réparation.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Modale d'Annulation -->
        <div x-show="cancelModalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200 space-y-4">
                <div class="flex items-center space-x-2 text-rose-600 font-bold text-base">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Annulation du Ticket {{ $ticket->reference }}</span>
                </div>
                <p class="text-xs text-slate-600">
                    L'annulation est une action définitive. Un motif d'annulation obligatoire doit être consigné dans l'historique d'audit.
                </p>

                <form action="{{ route('tickets.transition', $ticket) }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="status" value="ANNULE">
                    <div>
                        <label for="reason" class="block text-xs font-semibold text-slate-700 mb-1">Motif d'annulation *</label>
                        <textarea id="reason" name="reason" rows="3" required placeholder="ex: Client a refusé le devis et repris l'appareil sans réparation..."
                                  class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-rose-500 transition"></textarea>
                    </div>

                    <div class="flex items-center justify-end space-x-2 pt-2 border-t border-slate-100">
                        <button type="button" @click="cancelModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800">
                            Fermer
                        </button>
                        <button type="submit" class="px-4 py-2 text-xs font-semibold bg-rose-600 hover:bg-rose-700 text-white rounded-lg transition">
                            Confirmer l'annulation
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modale d'Intervention / Diagnostic -->
        <div x-show="interventionModalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
            <div class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-xl border border-slate-200 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                    <h3 class="text-base font-bold text-slate-900">Consigner une Intervention / Diagnostic</h3>
                    <button type="button" @click="interventionModalOpen = false" class="text-slate-400 hover:text-slate-600">&times;</button>
                </div>

                <form action="{{ route('tickets.interventions.store', $ticket) }}" method="POST" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="intervention_type" class="block text-xs font-semibold text-slate-700 mb-1">Type d'opération *</label>
                            <select id="intervention_type" name="intervention_type" required class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg">
                                <option value="DIAGNOSTIC">Diagnostic</option>
                                <option value="OPERATION">Réparation / Opération</option>
                                <option value="TEST">Contrôle / Test qualité</option>
                                <option value="AUTRE">Autre</option>
                            </select>
                        </div>
                        <div>
                            <label for="duration_minutes" class="block text-xs font-semibold text-slate-700 mb-1">Durée (minutes)</label>
                            <input id="duration_minutes" type="number" name="duration_minutes" placeholder="ex: 45" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg">
                        </div>
                    </div>

                    <div>
                        <label for="work_performed" class="block text-xs font-semibold text-slate-700 mb-1">Travail effectué / Compte-rendu *</label>
                        <textarea id="work_performed" name="work_performed" rows="2" required placeholder="Description des travaux réalisés..." class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg"></textarea>
                    </div>

                    <div>
                        <label for="diagnosis" class="block text-xs font-semibold text-slate-700 mb-1">Diagnostic technique & cause identifiée</label>
                        <textarea id="diagnosis" name="diagnosis" rows="2" placeholder="Détails de la panne identifiée..." class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg"></textarea>
                    </div>

                    <div>
                        <label for="test_results" class="block text-xs font-semibold text-slate-700 mb-1">Résultats des tests après intervention</label>
                        <input id="test_results" type="text" name="test_results" placeholder="ex: Démarrage OK, température CPU 45°C stabilisée..." class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg">
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="customer_summary" class="block text-xs font-semibold text-slate-700">Synthèse destinée au client (visible sur le suivi en ligne)</label>
                            <button type="button" @click="generateAiClientSummary()" :disabled="aiGenerating" class="inline-flex items-center px-2 py-0.5 rounded bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-[11px] font-semibold transition">
                                <span x-show="!aiGenerating">✨ Copilot : Générer synthèse client</span>
                                <span x-show="aiGenerating">Génération en cours...</span>
                            </button>
                        </div>
                        <input id="customer_summary" type="text" name="customer_summary" placeholder="ex: Nettoyage et remplacement pâte thermique effectués." class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg">
                    </div>

                    <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="interventionModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800">
                            Annuler
                        </button>
                        <button type="submit" class="px-4 py-2 text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                            Enregistrer l'intervention
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
