@extends('layouts.app')

@section('title', $contact->name)
@section('page-title', $contact->name)

@section('header-actions')
<a href="{{ route('contacts.edit', $contact) }}" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 flex items-center gap-1">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
    Editar
</a>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Main --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Contact Card --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-start gap-4">
                <div class="w-16 h-16 rounded-full flex items-center justify-center text-xl font-bold {{ $contact->avatarUrl ? '' : 'bg-primary-100 text-primary-600' }}">
                    @if($contact->avatarUrl)
                        <img src="{{ $contact->avatarUrl }}" class="w-16 h-16 rounded-full object-cover">
                    @else
                        {{ $contact->initials }}
                    @endif
                </div>
                <div class="flex-1">
                    <h2 class="text-xl font-bold text-gray-900">{{ $contact->name }}</h2>
                    @if($contact->company)
                    <p class="text-sm text-gray-500">{{ $contact->company }}</p>
                    @endif
                    <div class="mt-3 flex flex-wrap gap-4 text-sm">
                        @if($contact->phone)
                        <div class="flex items-center gap-1 text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            {{ $contact->phone }}
                        </div>
                        @endif
                        @if($contact->email)
                        <div class="flex items-center gap-1 text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            {{ $contact->email }}
                        </div>
                        @endif
                        @if($contact->document)
                        <div class="flex items-center gap-1 text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0"/></svg>
                            {{ $contact->document }}
                        </div>
                        @endif
                    </div>
                    @if($contact->tags)
                    <div class="mt-3 flex flex-wrap gap-1">
                        @foreach((is_array($contact->tags) ? $contact->tags : explode(',', $contact->tags)) as $tag)
                        <span class="badge bg-primary-50 text-primary-700 text-xs">{{ trim($tag) }}</span>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Conversations --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Conversas ({{ $conversations->count() }})</h3>
            @if($conversations->count() > 0)
            <div class="divide-y">
                @foreach($conversations as $conv)
                <a href="{{ route('inbox.index', ['conversation' => $conv->id]) }}" class="flex items-center justify-between py-3 hover:bg-gray-50 -mx-2 px-2 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs" style="background-color: {{ $conv->channel->color ?? '#e5e7eb' }}20; color: {{ $conv->channel->color ?? '#6b7280' }}">
                            {{ strtoupper(substr($conv->channel->type ?? '?', 0, 2)) }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $conv->channel->name ?? 'Canal' }}</p>
                            <p class="text-xs text-gray-500">{{ $conv->last_message_at?->diffForHumans() ?? $conv->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    <span class="badge {{ $conv->status === 'open' ? 'bg-green-100 text-green-700' : ($conv->status === 'new' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600') }}">
                        {{ ucfirst($conv->status) }}
                    </span>
                </a>
                @endforeach
            </div>
            @else
            <p class="text-sm text-gray-500 text-center py-4">Nenhuma conversa ainda</p>
            @endif
        </div>

        {{-- Leads --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Leads ({{ $leads->count() }})</h3>
            @if($leads->count() > 0)
            <div class="divide-y">
                @foreach($leads as $lead)
                <a href="{{ route('leads.show', $lead) }}" class="flex items-center justify-between py-3 hover:bg-gray-50 -mx-2 px-2 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $lead->vehicleInterest ?? 'Lead #' . $lead->id }}</p>
                        <p class="text-xs text-gray-500">{{ $lead->created_at->format('d/m/Y') }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($lead->estimated_value)
                        <span class="text-sm font-medium text-gray-700">R$ {{ number_format($lead->estimated_value, 0, ',', '.') }}</span>
                        @endif
                        <span class="badge {{ $lead->stageColor }}">{{ $lead->stageLabel }}</span>
                    </div>
                </a>
                @endforeach
            </div>
            @else
            <p class="text-sm text-gray-500 text-center py-4">Nenhum lead ainda</p>
            @endif
        </div>

        {{-- Tickets --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Tickets ({{ $tickets->count() }})</h3>
            @if($tickets->count() > 0)
            <div class="divide-y">
                @foreach($tickets as $ticket)
                <a href="{{ route('tickets.show', $ticket) }}" class="flex items-center justify-between py-3 hover:bg-gray-50 -mx-2 px-2 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $ticket->ticket_number }} - {{ $ticket->subject }}</p>
                        <p class="text-xs text-gray-500">{{ $ticket->created_at->format('d/m/Y') }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        {!! $ticket->priorityBadge !!}
                        {!! $ticket->statusBadge !!}
                    </div>
                </a>
                @endforeach
            </div>
            @else
            <p class="text-sm text-gray-500 text-center py-4">Nenhum ticket ainda</p>
            @endif
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Detalhes</h3>
            <dl class="space-y-3">
                @if($contact->city || $contact->state)
                <div class="flex justify-between text-sm">
                    <dt class="text-gray-500">Localização</dt>
                    <dd class="font-medium text-gray-900">{{ collect([$contact->city, $contact->state])->filter()->join(', ') }}</dd>
                </div>
                @endif
                <div class="flex justify-between text-sm">
                    <dt class="text-gray-500">Cadastrado em</dt>
                    <dd class="font-medium text-gray-900">{{ $contact->created_at->format('d/m/Y H:i') }}</dd>
                </div>
                <div class="flex justify-between text-sm">
                    <dt class="text-gray-500">Última interação</dt>
                    <dd class="font-medium text-gray-900">{{ $contact->updated_at->diffForHumans() }}</dd>
                </div>
            </dl>
        </div>

        @if($contact->notes)
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Observações</h3>
            <p class="text-sm text-gray-600">{{ $contact->notes }}</p>
        </div>
        @endif

        {{-- Quick Actions --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Ações Rápidas</h3>
            <div class="space-y-2">
                <a href="{{ route('leads.create', ['contact_id' => $contact->id]) }}" class="w-full flex items-center gap-2 px-3 py-2 text-sm text-gray-700 rounded-lg hover:bg-gray-50">
                    <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Criar Lead
                </a>
                <a href="{{ route('tickets.create', ['contact_id' => $contact->id]) }}" class="w-full flex items-center gap-2 px-3 py-2 text-sm text-gray-700 rounded-lg hover:bg-gray-50">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Criar Ticket
                </a>
            </div>
        </div>
    </div>
</div>
@endsection