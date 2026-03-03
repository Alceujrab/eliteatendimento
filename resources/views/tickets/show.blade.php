@extends('layouts.app')

@section('title', $ticket->number . ' - ' . $ticket->subject)
@section('page-title', $ticket->number)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Main --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Ticket Info --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">{{ $ticket->subject }}</h2>
                    <p class="text-sm text-gray-500 mt-1">Criado por {{ $ticket->contact->name ?? '-' }} em {{ $ticket->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="badge {{ $ticket->priorityBadge }}">{{ $ticket->priorityLabel }}</span>
                    <span class="badge {{ $ticket->statusBadge }}">{{ $ticket->statusLabel }}</span>
                </div>
            </div>
            <div class="prose prose-sm max-w-none text-gray-700">
                {!! nl2br(e($ticket->body)) !!}
            </div>
        </div>

        {{-- Comments --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Comentários</h3>

            <div class="space-y-4 mb-6">
                @forelse($ticket->comments()->with('user')->oldest()->get() as $comment)
                <div class="flex gap-3 {{ $comment->is_internal ? 'bg-amber-50 -mx-2 px-2 py-2 rounded-lg' : '' }}">
                    <div class="w-8 h-8 bg-primary-100 text-primary-700 rounded-full flex items-center justify-center text-xs font-semibold shrink-0">
                        {{ strtoupper(substr($comment->user->name ?? 'S', 0, 2)) }}
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-gray-900">{{ $comment->user->name ?? 'Sistema' }}</span>
                            @if($comment->is_internal)
                                <span class="badge bg-amber-100 text-amber-700">Interno</span>
                            @endif
                            <span class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="text-sm text-gray-700 mt-1">{!! nl2br(e($comment->body)) !!}</div>
                    </div>
                </div>
                @empty
                <p class="text-sm text-gray-500 text-center py-4">Nenhum comentário ainda</p>
                @endforelse
            </div>

            {{-- Add Comment --}}
            <form method="POST" action="{{ route('tickets.add-comment', $ticket) }}" class="border-t border-gray-100 pt-4">
                @csrf
                <textarea name="body" rows="3" required placeholder="Adicione um comentário..."
                          class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500 mb-3"></textarea>
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" name="is_internal" value="1" class="rounded border-gray-300 text-amber-500 focus:ring-amber-500">
                        Nota interna (não visível ao cliente)
                    </label>
                    <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary-700">
                        Comentar
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">
        {{-- Status Actions --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Atualizar Status</h3>
            <div class="flex flex-wrap gap-2">
                @foreach(['open' => 'Aberto', 'in_progress' => 'Em Progresso', 'waiting_customer' => 'Aguardando', 'resolved' => 'Resolvido', 'closed' => 'Fechado'] as $s => $l)
                    @if($s !== $ticket->status)
                    <form method="POST" action="{{ route('tickets.update-status', $ticket) }}" class="inline">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="{{ $s }}">
                        <button class="px-3 py-1.5 text-xs rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors font-medium">{{ $l }}</button>
                    </form>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Details --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Detalhes</h3>
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-xs text-gray-500">Categoria</dt>
                    <dd><span class="badge {{ $ticket->categoryBadge }}">{{ $ticket->categoryLabel }}</span></dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Responsável</dt>
                    <dd class="text-gray-900">{{ $ticket->assignedUser->name ?? 'Não atribuído' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">SLA - Vencimento</dt>
                    <dd class="{{ $ticket->isSlaOverdue ? 'text-red-600 font-medium' : 'text-gray-900' }}">
                        {{ $ticket->due_at ? $ticket->due_at->format('d/m/Y H:i') : 'N/A' }}
                        @if($ticket->isSlaOverdue) (VENCIDO) @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Primeira Resposta</dt>
                    <dd class="text-gray-900">{{ $ticket->first_response_at ? $ticket->first_response_at->format('d/m/Y H:i') : 'Aguardando' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Resolvido em</dt>
                    <dd class="text-gray-900">{{ $ticket->resolved_at ? $ticket->resolved_at->format('d/m/Y H:i') : '-' }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection