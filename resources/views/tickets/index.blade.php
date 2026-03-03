@extends('layouts.app')

@section('title', 'Tickets')
@section('page-title', 'Tickets')

@section('header-actions')
<a href="{{ route('tickets.create') }}" class="bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    Novo Ticket
</a>
@endsection

@section('content')
<div class="space-y-4">
    {{-- Status tabs --}}
    <div class="flex items-center gap-2 overflow-x-auto">
        @php
            $tabs = ['all' => 'Todos', 'open' => 'Abertos', 'in_progress' => 'Em Progresso', 'waiting_customer' => 'Aguardando Cliente', 'resolved' => 'Resolvidos', 'closed' => 'Fechados'];
            $currentTab = request('status', 'all');
        @endphp
        @foreach($tabs as $key => $label)
            <a href="{{ route('tickets.index', $key === 'all' ? [] : ['status' => $key]) }}"
               class="shrink-0 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors flex items-center gap-1.5
               {{ $currentTab === $key || ($key === 'all' && !request('status')) ? 'bg-primary-100 text-primary-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                {{ $label }}
                @if(isset($statusCounts[$key === 'all' ? '' : $key]))
                    <span class="text-xs opacity-70">({{ $statusCounts[$key] ?? 0 }})</span>
                @endif
            </a>
        @endforeach
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Ticket</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Assunto</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Contato</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Categoria</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Prioridade</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">SLA</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Responsável</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($tickets as $ticket)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <a href="{{ route('tickets.show', $ticket) }}" class="text-sm font-mono text-primary-600 hover:text-primary-700">{{ $ticket->number }}</a>
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('tickets.show', $ticket) }}" class="text-sm font-medium text-gray-900 hover:text-primary-600">{{ Str::limit($ticket->subject, 50) }}</a>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $ticket->contact->name ?? '-' }}</td>
                    <td class="px-4 py-3"><span class="badge {{ $ticket->categoryBadge }}">{{ $ticket->categoryLabel }}</span></td>
                    <td class="px-4 py-3"><span class="badge {{ $ticket->priorityBadge }}">{{ $ticket->priorityLabel }}</span></td>
                    <td class="px-4 py-3"><span class="badge {{ $ticket->statusBadge }}">{{ $ticket->statusLabel }}</span></td>
                    <td class="px-4 py-3">
                        @if($ticket->due_at)
                            @if($ticket->isSlaOverdue)
                                <span class="text-xs text-red-600 font-medium">Vencido</span>
                            @else
                                <span class="text-xs text-gray-500">{{ $ticket->due_at->diffForHumans() }}</span>
                            @endif
                        @else
                            <span class="text-xs text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $ticket->assignedUser->name ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-12 text-center text-gray-500">Nenhum ticket encontrado</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $tickets->links() }}
</div>
@endsection