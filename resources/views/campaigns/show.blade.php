@extends('layouts.app')

@section('title', $campaign->name)
@section('page-title', 'Campanha: ' . $campaign->name)

@section('header-actions')
@if($campaign->status === 'draft' || $campaign->status === 'paused')
<form method="POST" action="{{ route('campaigns.send', $campaign) }}" class="inline">
    @csrf
    <button class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700 flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Iniciar Envio
    </button>
</form>
@endif
@if($campaign->status === 'running')
<form method="POST" action="{{ route('campaigns.pause', $campaign) }}" class="inline">
    @csrf
    <button class="bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-amber-700">Pausar</button>
</form>
@endif
@if(!in_array($campaign->status, ['completed', 'cancelled']))
<form method="POST" action="{{ route('campaigns.cancel', $campaign) }}" class="inline">
    @csrf
    <button class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700" onclick="return confirm('Cancelar esta campanha?')">Cancelar</button>
</form>
@endif
@endsection

@section('content')
<div class="space-y-6">
    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
        <div class="stat-card">
            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total']) }}</p>
            <p class="text-sm text-gray-500">Total</p>
        </div>
        <div class="stat-card">
            <p class="text-2xl font-bold text-blue-600">{{ number_format($stats['sent']) }}</p>
            <p class="text-sm text-gray-500">Enviados</p>
        </div>
        <div class="stat-card">
            <p class="text-2xl font-bold text-green-600">{{ number_format($stats['delivered']) }}</p>
            <p class="text-sm text-gray-500">Entregues</p>
        </div>
        <div class="stat-card">
            <p class="text-2xl font-bold text-purple-600">{{ number_format($stats['read']) }}</p>
            <p class="text-sm text-gray-500">Lidos</p>
        </div>
        <div class="stat-card">
            <p class="text-2xl font-bold text-red-600">{{ number_format($stats['failed']) }}</p>
            <p class="text-sm text-gray-500">Falhas</p>
        </div>
    </div>

    {{-- Campaign Details --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-3">Mensagem</h3>
            <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-700 whitespace-pre-wrap">{{ $campaign->message_template }}</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Informações</h3>
            <dl class="space-y-3 text-sm">
                <div><dt class="text-xs text-gray-500">Status</dt><dd><span class="badge {{ $campaign->statusBadge }}">{{ ucfirst($campaign->status) }}</span></dd></div>
                <div><dt class="text-xs text-gray-500">Tipo</dt><dd class="text-gray-900 uppercase">{{ $campaign->type }}</dd></div>
                <div><dt class="text-xs text-gray-500">Criada em</dt><dd class="text-gray-900">{{ $campaign->created_at->format('d/m/Y H:i') }}</dd></div>
                @if($campaign->started_at)<div><dt class="text-xs text-gray-500">Iniciada em</dt><dd class="text-gray-900">{{ $campaign->started_at->format('d/m/Y H:i') }}</dd></div>@endif
                @if($campaign->completed_at)<div><dt class="text-xs text-gray-500">Concluída em</dt><dd class="text-gray-900">{{ $campaign->completed_at->format('d/m/Y H:i') }}</dd></div>@endif
            </dl>
        </div>
    </div>
</div>
@endsection