<x-layouts.app>
    <x-slot:title>{{ $device->brand }} {{ $device->model }}</x-slot:title>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">{{ $device->brand }} {{ $device->model }}</h1>
                    <div class="flex items-center space-x-2 text-xs text-slate-500 mt-0.5">
                        <span class="font-medium text-slate-700">{{ $device->category_label }}</span>
                        <span>&bull;</span>
                        <span>Propriétaire : <a href="{{ route('customers.show', $device->customer) }}" class="text-blue-600 hover:underline font-semibold">{{ $device->customer->name }}</a></span>
                    </div>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('devices.edit', $device) }}" class="px-3.5 py-1.5 rounded-xl border border-slate-300 text-slate-700 font-medium text-xs hover:bg-slate-100 transition">
                    Modifier l'appareil
                </a>
                <a href="{{ route('tickets.create', ['customer_id' => $device->customer_id, 'device_id' => $device->id]) }}" class="px-3.5 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-sm transition">
                    + Ouvrir un Ticket de Réparation
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Fiche technique & état (1 col) -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-200 pb-3">
                    <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Caractéristiques</h2>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-slate-400 block font-medium">Numéro de série / Tag</span>
                        <span class="font-mono font-semibold text-slate-800 text-sm">{{ $device->serial_number ?: 'Non spécifié' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Accessoires habituels</span>
                        <span class="text-slate-700">{{ $device->accessories ?: 'Aucun accessoire' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">État esthétique habituel</span>
                        <span class="text-slate-700">{{ $device->aesthetic_condition ?: 'Bon état général' }}</span>
                    </div>
                    @if($device->notes)
                    <div>
                        <span class="text-slate-400 block font-medium">Notes & Remarques</span>
                        <p class="text-slate-600 mt-0.5 whitespace-pre-line">{{ $device->notes }}</p>
                    </div>
                    @endif
                </div>

                <!-- Section Photos de l'appareil -->
                <div class="border-t border-slate-200 pt-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-slate-900">Photos de l'appareil</span>
                    </div>
                    <form action="{{ route('attachments.store') }}" method="POST" enctype="multipart/form-data" class="space-y-2">
                        @csrf
                        <input type="hidden" name="attachable_type" value="App\Models\Device">
                        <input type="hidden" name="attachable_id" value="{{ $device->id }}">
                        <input type="hidden" name="file_category" value="DEVICE_PHOTO">
                        <input type="file" name="file" accept="image/jpeg,image/png,image/webp,application/pdf" required class="text-xs w-full">
                        <button type="submit" class="w-full py-1.5 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition">
                            + Téléverser une photo
                        </button>
                    </form>

                    <div class="grid grid-cols-2 gap-2 mt-3">
                        @forelse($device->attachments as $att)
                        <div class="p-2 border border-slate-200 rounded-lg text-xs relative group bg-slate-50">
                            <a href="{{ route('attachments.show', $att) }}" target="_blank" class="text-blue-600 hover:underline block truncate font-medium">
                                {{ $att->original_filename }}
                            </a>
                            <span class="text-[10px] text-slate-400 block">{{ $att->humanFileSize() }}</span>
                        </div>
                        @empty
                        <div class="col-span-2 text-center text-slate-400 text-[11px] py-2">
                            Aucune photo enregistrée
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Historique des réparations (2 cols) -->
            <div class="md:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Historique des Interventions sur cet Équipement</h2>
                        <p class="text-xs text-slate-500">Traçabilité complète des pannes et réparations passées</p>
                    </div>
                </div>

                @if($device->repairTickets->isEmpty())
                <div class="p-10 text-center text-slate-400">
                    <p class="text-sm font-medium text-slate-600">Aucun ticket de réparation ouvert sur cet équipement.</p>
                    <a href="{{ route('tickets.create', ['customer_id' => $device->customer_id, 'device_id' => $device->id]) }}" class="mt-3 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-xl text-xs font-semibold">
                        Ouvrir le premier ticket
                    </a>
                </div>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-2.5">Réf & Date</th>
                                <th class="px-4 py-2.5">Problème déclaré</th>
                                <th class="px-4 py-2.5">Technicien</th>
                                <th class="px-4 py-2.5">Statut</th>
                                <th class="px-4 py-2.5 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($device->repairTickets as $ticket)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-5 py-3 font-semibold text-slate-900 whitespace-nowrap">
                                    <a href="{{ route('tickets.show', $ticket) }}" class="text-blue-600 hover:underline">
                                        {{ $ticket->reference }}
                                    </a>
                                    <div class="text-[11px] text-slate-400 font-normal">{{ $ticket->deposit_date->format('d/m/Y') }}</div>
                                </td>
                                <td class="px-4 py-3 max-w-xs truncate text-slate-600">
                                    {{ $ticket->reported_issue }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-500">
                                    {{ $ticket->assignedTo?->name ?: 'Non assigné' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium border {{ $ticket->status->badgeClasses() }}">
                                        {{ $ticket->status->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('tickets.show', $ticket) }}" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 rounded text-slate-700 font-medium transition text-[11px]">
                                        Détails
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
