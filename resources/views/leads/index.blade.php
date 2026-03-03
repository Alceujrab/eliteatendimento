@extends('layouts.app')

@section('title', 'Leads / CRM')
@section('page-title', 'Leads / CRM')

@section('header-actions')
<a href="{{ route('leads.create') }}" class="bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    Novo Lead
</a>
@endsection

@section('content')
<div x-data="{ view: 'kanban' }" class="space-y-4">
    {{-- View Toggle --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <button @click="view = 'kanban'" :class="view === 'kanban' ? 'bg-primary-100 text-primary-700' : 'text-gray-600 hover:bg-gray-100'"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7"/></svg>
                Kanban
            </button>
            <button @click="view = 'list'" :class="view === 'list' ? 'bg-primary-100 text-primary-700' : 'text-gray-600 hover:bg-gray-100'"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                Lista
            </button>
        </div>
    </div>

    {{-- Kanban View --}}
    <div x-show="view === 'kanban'" class="flex gap-4 overflow-x-auto pb-4">
        @php
            $stages = \App\Models\Lead::stages();
            $stageColors = ['new' => 'blue', 'qualified' => 'cyan', 'proposal' => 'amber', 'negotiation' => 'purple', 'won' => 'green', 'lost' => 'red'];
        @endphp

        @foreach($stages as $stageKey => $stageLabel)
        @php $stageLeads = $leadsByStage[$stageKey] ?? collect(); $color = $stageColors[$stageKey]; @endphp
        <div class="shrink-0 w-72 bg-gray-50 rounded-xl p-3" id="stage-{{ $stageKey }}"
             x-data x-init="Sortable.create($el.querySelector('.kanban-list'), {
                 group: 'leads', animation: 150,
                 onEnd: function(evt) {
                     const leadId = evt.item.dataset.id;
                     const newStage = evt.to.closest('[id^=stage-]').id.replace('stage-', '');
                     fetch('/leads/' + leadId + '/stage', {
                         method: 'PATCH', headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                         body: JSON.stringify({ stage: newStage })
                     });
                 }
             })">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-{{ $color }}-500"></span>
                    {{ $stageLabel }}
                </h3>
                <span class="text-xs text-gray-500 bg-gray-200 px-2 py-0.5 rounded-full">{{ $stageLeads->count() }}</span>
            </div>
            <div class="kanban-list space-y-2 min-h-[60px]">
                @foreach($stageLeads as $lead)
                <div class="kanban-card" data-id="{{ $lead->id }}">
                    <div class="flex items-center justify-between mb-2">
                        <a href="{{ route('leads.show', $lead) }}" class="text-sm font-medium text-gray-900 hover:text-primary-600 truncate">
                            {{ $lead->contact->name ?? 'Sem contato' }}
                        </a>
                        @if($lead->temperature)
                        <span class="badge {{ $lead->temperatureColor }}">{{ $lead->temperatureLabel }}</span>
                        @endif
                    </div>
                    @if($lead->vehicle_interest)
                    <p class="text-xs text-gray-500 mb-2">{{ $lead->vehicle_interest }}</p>
                    @endif
                    <div class="flex items-center justify-between">
                        @if($lead->estimated_value)
                        <span class="text-xs font-medium text-green-600">R$ {{ number_format($lead->estimated_value, 0, ',', '.') }}</span>
                        @else
                        <span></span>
                        @endif
                        @if($lead->assignedUser)
                        <div class="w-6 h-6 bg-primary-100 text-primary-700 rounded-full flex items-center justify-center text-[10px] font-semibold" title="{{ $lead->assignedUser->name }}">
                            {{ strtoupper(substr($lead->assignedUser->name, 0, 2)) }}
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

    {{-- List View --}}
    <div x-show="view === 'list'" class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Contato</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Veículo</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Estágio</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Temp.</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Valor</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Responsável</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Data</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($leadsByStage->flatten() as $lead)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <a href="{{ route('leads.show', $lead) }}" class="font-medium text-gray-900 hover:text-primary-600">
                            {{ $lead->contact->name ?? 'Sem contato' }}
                        </a>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $lead->vehicle_interest ?? '-' }}</td>
                    <td class="px-4 py-3"><span class="badge {{ $lead->stageColor }}">{{ $lead->stageLabel }}</span></td>
                    <td class="px-4 py-3"><span class="badge {{ $lead->temperatureColor }}">{{ $lead->temperatureLabel }}</span></td>
                    <td class="px-4 py-3 text-gray-900">{{ $lead->estimated_value ? 'R$ ' . number_format($lead->estimated_value, 0, ',', '.') : '-' }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $lead->assignedUser->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-400">{{ $lead->created_at->format('d/m/Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection