@extends('layouts.app')

@section('title', 'Lead - ' . ($lead->contact->name ?? 'Sem contato'))
@section('page-title', 'Detalhes do Lead')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Main Info --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Lead Card --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-primary-100 text-primary-700 rounded-full flex items-center justify-center text-xl font-bold">
                        {{ $lead->contact ? strtoupper(substr($lead->contact->name, 0, 2)) : '??' }}
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">{{ $lead->contact->name ?? 'Sem contato' }}</h2>
                        <p class="text-sm text-gray-500">{{ $lead->contact->phone ?? '' }} {{ $lead->contact->email ? '· ' . $lead->contact->email : '' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="badge {{ $lead->stageColor }}">{{ $lead->stageLabel }}</span>
                    <span class="badge {{ $lead->temperatureColor }}">{{ $lead->temperatureLabel }}</span>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6">
                <div>
                    <p class="text-xs text-gray-500 mb-1">Veículo de Interesse</p>
                    <p class="text-sm font-medium text-gray-900">{{ $lead->vehicle_interest ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Valor Estimado</p>
                    <p class="text-sm font-medium text-gray-900">{{ $lead->estimated_value ? 'R$ ' . number_format($lead->estimated_value, 0, ',', '.') : '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Origem</p>
                    <p class="text-sm font-medium text-gray-900 capitalize">{{ $lead->source ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Responsável</p>
                    <p class="text-sm font-medium text-gray-900">{{ $lead->assignedUser->name ?? '-' }}</p>
                </div>
            </div>

            {{-- Stage Actions --}}
            <div class="flex items-center gap-2 mt-6 pt-4 border-t border-gray-100">
                <span class="text-xs text-gray-500 mr-2">Mover para:</span>
                @foreach(\App\Models\Lead::stages() as $sk => $sl)
                    @if($sk !== $lead->stage)
                    <form method="POST" action="{{ route('leads.update-stage', $lead) }}" class="inline">
                        @csrf @method('PATCH')
                        <input type="hidden" name="stage" value="{{ $sk }}">
                        <button class="px-2.5 py-1 text-xs rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">{{ $sl }}</button>
                    </form>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Activity Timeline --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Atividades</h3>

            {{-- Add Activity --}}
            <form method="POST" action="{{ route('leads.add-activity', $lead) }}" class="mb-6">
                @csrf
                <div class="flex gap-2">
                    <select name="type" class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="note">Nota</option>
                        <option value="call">Ligação</option>
                        <option value="whatsapp">WhatsApp</option>
                        <option value="email">E-mail</option>
                        <option value="meeting">Reunião</option>
                        <option value="follow_up">Follow-up</option>
                    </select>
                    <input type="text" name="description" placeholder="Descreva a atividade..." required
                           class="flex-1 rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary-700">
                        Adicionar
                    </button>
                </div>
            </form>

            {{-- Timeline --}}
            <div class="space-y-4">
                @forelse($lead->activities()->with('user')->latest()->get() as $activity)
                <div class="flex gap-3">
                    <div class="shrink-0 mt-1">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm
                            {{ $activity->type === 'stage_change' ? 'bg-purple-100 text-purple-600' : 'bg-gray-100 text-gray-600' }}">
                            {!! $activity->icon !!}
                        </div>
                    </div>
                    <div class="flex-1 pb-4 border-b border-gray-100 last:border-0">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-gray-900">{{ $activity->description }}</p>
                            <span class="text-xs text-gray-400">{{ $activity->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $activity->user->name ?? 'Sistema' }}</p>
                    </div>
                </div>
                @empty
                <p class="text-sm text-gray-500 text-center py-4">Nenhuma atividade registrada</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">
        {{-- Quick Info --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Informações</h3>
            <dl class="space-y-3">
                <div>
                    <dt class="text-xs text-gray-500">Criado em</dt>
                    <dd class="text-sm text-gray-900">{{ $lead->created_at->format('d/m/Y H:i') }}</dd>
                </div>
                @if($lead->won_at)
                <div>
                    <dt class="text-xs text-gray-500">Ganho em</dt>
                    <dd class="text-sm text-green-600 font-medium">{{ $lead->won_at->format('d/m/Y H:i') }}</dd>
                </div>
                @endif
                @if($lead->lost_at)
                <div>
                    <dt class="text-xs text-gray-500">Perdido em</dt>
                    <dd class="text-sm text-red-600 font-medium">{{ $lead->lost_at->format('d/m/Y H:i') }}</dd>
                </div>
                @endif
                @if($lead->lost_reason)
                <div>
                    <dt class="text-xs text-gray-500">Razão da perda</dt>
                    <dd class="text-sm text-gray-900">{{ $lead->lost_reason }}</dd>
                </div>
                @endif
            </dl>
        </div>

        {{-- Contact conversations --}}
        @if($lead->contact && $lead->contact->conversations->count() > 0)
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Conversas do Contato</h3>
            <div class="space-y-2">
                @foreach($lead->contact->conversations->take(5) as $conv)
                <a href="{{ route('inbox.show', $conv) }}" class="flex items-center gap-2 text-sm text-gray-600 hover:text-primary-600">
                    <span class="w-2 h-2 rounded-full {{ $conv->status === 'resolved' ? 'bg-green-400' : 'bg-amber-400' }}"></span>
                    {{ $conv->channel->name ?? 'Canal' }} - {{ $conv->status }}
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection