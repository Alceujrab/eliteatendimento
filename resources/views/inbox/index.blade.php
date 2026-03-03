@extends('layouts.app')

@section('title', 'Inbox')
@section('page-title', 'Inbox')

@section('content')
<div class="flex h-[calc(100vh-10rem)] bg-white rounded-xl border border-gray-200 overflow-hidden">
    {{-- Conversation List --}}
    <div class="w-80 border-r border-gray-200 flex flex-col shrink-0">
        {{-- Filters --}}
        <div class="p-3 border-b border-gray-200 space-y-2">
            <form method="GET" class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar conversa..."
                       class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
            </form>
            <div class="flex gap-1.5 overflow-x-auto">
                @php
                    $statuses = ['all' => 'Todos', 'new' => 'Novos', 'open' => 'Abertos', 'pending' => 'Pendentes', 'resolved' => 'Resolvidos'];
                    $currentStatus = request('status', 'all');
                @endphp
                @foreach($statuses as $key => $label)
                    <a href="{{ route('inbox.index', array_merge(request()->query(), ['status' => $key === 'all' ? null : $key])) }}"
                       class="shrink-0 px-2.5 py-1 text-xs font-medium rounded-full transition-colors {{ $currentStatus === $key || ($key === 'all' && !request('status')) ? 'bg-primary-100 text-primary-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Conversation items --}}
        <div class="flex-1 overflow-y-auto scrollbar-thin">
            @forelse($conversations as $conv)
            <a href="{{ route('inbox.show', $conv) }}"
               class="flex items-start gap-3 px-4 py-3 border-b border-gray-100 hover:bg-gray-50 transition-colors {{ isset($current) && $current->id === $conv->id ? 'bg-primary-50 border-l-2 border-l-primary-600' : '' }}">
                <div class="relative shrink-0">
                    <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center text-sm font-semibold text-gray-600">
                        {{ $conv->contact ? strtoupper(substr($conv->contact->name, 0, 2)) : '??' }}
                    </div>
                    @if($conv->channel)
                    <div class="absolute -bottom-0.5 -right-0.5 w-4 h-4 rounded-full flex items-center justify-center text-white text-[8px]"
                         style="background-color: {{ $conv->channel->color_attribute }}">
                        {{ strtoupper(substr($conv->channel->type, 0, 1)) }}
                    </div>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $conv->contact->name ?? 'Desconhecido' }}</p>
                        <span class="text-[11px] text-gray-400 shrink-0">{{ $conv->last_message_at?->format('H:i') }}</span>
                    </div>
                    <p class="text-xs text-gray-500 truncate mt-0.5">{{ $conv->lastMessage->body ?? '' }}</p>
                    <div class="flex items-center gap-2 mt-1">
                        @if($conv->status === 'new')
                            <span class="badge bg-blue-100 text-blue-700">Novo</span>
                        @elseif($conv->status === 'pending')
                            <span class="badge bg-amber-100 text-amber-700">Pendente</span>
                        @endif
                        @if($conv->unread_count > 0)
                            <span class="ml-auto bg-primary-600 text-white text-[10px] font-bold rounded-full w-5 h-5 flex items-center justify-center">{{ $conv->unread_count }}</span>
                        @endif
                    </div>
                </div>
            </a>
            @empty
            <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                <p class="text-sm">Nenhuma conversa encontrada</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Chat Area --}}
    <div class="flex-1 flex flex-col">
        @if(isset($current))
            {{-- Chat Header --}}
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center text-sm font-semibold text-gray-600">
                        {{ $current->contact ? strtoupper(substr($current->contact->name, 0, 2)) : '??' }}
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $current->contact->name ?? 'Desconhecido' }}</p>
                        <p class="text-xs text-gray-500">{{ $current->contact->phone ?? '' }} &middot; {{ $current->channel->name ?? '' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    {{-- Assign --}}
                    <form method="POST" action="{{ route('inbox.assign', $current) }}" x-data>
                        @csrf
                        <select name="assigned_to" @change="$el.form.submit()"
                                class="text-xs rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500 py-1.5">
                            <option value="">Sem atribuição</option>
                            @foreach(\App\Models\User::where('tenant_id', auth()->user()->tenant_id)->where('is_active', true)->get() as $user)
                                <option value="{{ $user->id }}" {{ $current->assigned_to == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </form>
                    {{-- Status --}}
                    <form method="POST" action="{{ route('inbox.status', $current) }}" x-data>
                        @csrf @method('PATCH')
                        <select name="status" @change="$el.form.submit()"
                                class="text-xs rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500 py-1.5">
                            @foreach(['new' => 'Novo', 'open' => 'Aberto', 'pending' => 'Pendente', 'resolved' => 'Resolvido', 'archived' => 'Arquivado'] as $s => $l)
                                <option value="{{ $s }}" {{ $current->status === $s ? 'selected' : '' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>

            {{-- Messages --}}
            <div class="flex-1 overflow-y-auto p-4 space-y-3 scrollbar-thin" id="messagesContainer">
                @foreach($messages as $msg)
                <div class="flex {{ $msg->direction === 'outbound' ? 'justify-end' : 'justify-start' }}">
                    @if($msg->is_internal_note)
                        <div class="chat-bubble-internal">
                            <p class="text-xs text-amber-600 font-medium mb-1">Nota interna - {{ $msg->sender->name ?? 'Sistema' }}</p>
                            <p class="text-sm text-amber-800">{{ $msg->body }}</p>
                        </div>
                    @elseif($msg->direction === 'outbound')
                        <div class="chat-bubble-outbound">
                            <p class="text-sm">{{ $msg->body }}</p>
                            <p class="text-[10px] text-white/70 text-right mt-1">{{ $msg->created_at->format('H:i') }}</p>
                        </div>
                    @else
                        <div class="chat-bubble-inbound">
                            <p class="text-sm text-gray-900">{{ $msg->body }}</p>
                            <p class="text-[10px] text-gray-400 mt-1">{{ $msg->created_at->format('H:i') }}</p>
                        </div>
                    @endif
                </div>
                @endforeach
            </div>

            {{-- Send Message --}}
            <div class="border-t border-gray-200 p-3">
                <form method="POST" action="{{ route('inbox.send', $current) }}" class="flex gap-2">
                    @csrf
                    <input type="text" name="body" placeholder="Digite sua mensagem..." required autocomplete="off"
                           class="flex-1 rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500 text-sm">
                    <label class="flex items-center gap-1.5 text-xs text-gray-500">
                        <input type="checkbox" name="is_internal_note" value="1" class="rounded border-gray-300 text-amber-500 focus:ring-amber-500">
                        Nota
                    </label>
                    <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors">
                        Enviar
                    </button>
                </form>
            </div>
        @else
            {{-- Empty state --}}
            <div class="flex-1 flex flex-col items-center justify-center text-gray-400">
                <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                <p class="text-base font-medium">Selecione uma conversa</p>
                <p class="text-sm mt-1">Escolha uma conversa ao lado para começar</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    const container = document.getElementById('messagesContainer');
    if (container) container.scrollTop = container.scrollHeight;
</script>
@endpush
@endsection