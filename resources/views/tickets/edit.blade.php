<x-layouts.app>
    <x-slot:title>Modifier Ticket — {{ $ticket->reference }}</x-slot:title>

    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Modifier le Ticket {{ $ticket->reference }}</h1>
                <p class="text-sm text-slate-500 mt-0.5">Mise à jour des priorités, affectations et dates</p>
            </div>
            <a href="{{ route('tickets.show', $ticket) }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                &larr; Retour au ticket
            </a>
        </div>

        <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-xs">
            <form action="{{ route('tickets.update', $ticket) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="priority" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Priorité *</label>
                        <select id="priority" name="priority" required class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg">
                            @foreach(\App\Enums\TicketPriority::cases() as $pr)
                            <option value="{{ $pr->value }}" {{ old('priority', $ticket->priority->value) === $pr->value ? 'selected' : '' }}>
                                {{ $pr->label() }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="assigned_to_user_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Technicien assigné</label>
                        <select id="assigned_to_user_id" name="assigned_to_user_id" class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg">
                            <option value="">Non assigné</option>
                            @foreach($technicians as $tech)
                            <option value="{{ $tech->id }}" {{ old('assigned_to_user_id', $ticket->assigned_to_user_id) == $tech->id ? 'selected' : '' }}>
                                {{ $tech->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label for="estimated_completion_date" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Date estimée de fin
                    </label>
                    <input id="estimated_completion_date" type="datetime-local" name="estimated_completion_date"
                           value="{{ old('estimated_completion_date', $ticket->estimated_completion_date?->format('Y-m-d\TH:i')) }}"
                           class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg">
                </div>

                <div>
                    <label for="reported_issue" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Problème déclaré par le client *
                    </label>
                    <textarea id="reported_issue" name="reported_issue" rows="3" required
                              class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg">{{ old('reported_issue', $ticket->reported_issue) }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="intake_condition" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">État constaté</label>
                        <input id="intake_condition" type="text" name="intake_condition" value="{{ old('intake_condition', $ticket->intake_condition) }}"
                               class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg">
                    </div>
                    <div>
                        <label for="accessories_received" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Accessoires</label>
                        <input id="accessories_received" type="text" name="accessories_received" value="{{ old('accessories_received', $ticket->accessories_received) }}"
                               class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg">
                    </div>
                </div>

                <div>
                    <label for="internal_notes" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Notes internes</label>
                    <textarea id="internal_notes" name="internal_notes" rows="2" class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg">{{ old('internal_notes', $ticket->internal_notes) }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="device_access_code" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Code PIN / Session (Chiffré)</label>
                        <input id="device_access_code" type="text" name="device_access_code" value="{{ old('device_access_code', $ticket->decrypted_access_code) }}" placeholder="Laisser vide pour effacer..." class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg">
                    </div>
                    <div>
                        <label for="warranty_days" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Durée Garantie SAV (Jours)</label>
                        <input id="warranty_days" type="number" min="0" max="1095" name="warranty_days" value="{{ old('warranty_days', $ticket->warranty_days ?? 30) }}" class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg">
                    </div>
                </div>

                <div>
                    <label for="client_notes" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Notes client</label>
                    <textarea id="client_notes" name="client_notes" rows="2" class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-300 rounded-lg">{{ old('client_notes', $ticket->client_notes) }}</textarea>
                </div>

                <div class="pt-4 border-t border-slate-200 flex items-center justify-end space-x-3">
                    <a href="{{ route('tickets.show', $ticket) }}" class="px-4 py-2 text-sm font-medium text-slate-700 hover:text-slate-900">
                        Annuler
                    </a>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-xl shadow-sm transition">
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
