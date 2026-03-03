@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">
    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="stat-card">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $newLeadsToday }}</p>
            <p class="text-sm text-gray-500">Novos Leads Hoje</p>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $activeConversations }}</p>
            <p class="text-sm text-gray-500">Conversas Ativas</p>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $openTickets }}</p>
            <p class="text-sm text-gray-500">Tickets Abertos</p>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $avgResponseTime ? round($avgResponseTime) . 'min' : '--' }}</p>
            <p class="text-sm text-gray-500">Tempo Médio Resposta</p>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-pink-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $avgNps !== null ? number_format($avgNps, 1) : '--' }}</p>
            <p class="text-sm text-gray-500">NPS Médio</p>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Conversations by Channel --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Conversas por Canal</h3>
            <div class="h-64 flex items-center justify-center">
                <canvas id="channelChart"></canvas>
            </div>
        </div>

        {{-- Daily Volume --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Volume Diário (7 dias)</h3>
            <div class="h-64">
                <canvas id="volumeChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Bottom Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Leads by Stage --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Leads por Estágio</h3>
            <div class="space-y-3">
                @php
                    $stageLabels = ['new' => 'Novo', 'qualified' => 'Qualificado', 'proposal' => 'Proposta', 'negotiation' => 'Negociação', 'won' => 'Ganho', 'lost' => 'Perdido'];
                    $stageColors = ['new' => 'bg-blue-500', 'qualified' => 'bg-cyan-500', 'proposal' => 'bg-amber-500', 'negotiation' => 'bg-purple-500', 'won' => 'bg-green-500', 'lost' => 'bg-red-500'];
                    $totalLeads = $leadsByStage->sum() ?: 1;
                @endphp
                @foreach($stageLabels as $key => $label)
                    @php $count = $leadsByStage[$key] ?? 0; @endphp
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">{{ $label }}</span>
                            <span class="font-medium text-gray-900">{{ $count }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="{{ $stageColors[$key] }} rounded-full h-2" style="width: {{ ($count / $totalLeads) * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Unassigned Conversations --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-gray-900">Sem Atribuição</h3>
                <a href="{{ route('inbox.index') }}?status=new" class="text-sm text-primary-600 hover:text-primary-700">Ver todas</a>
            </div>
            @forelse($unassignedConversations as $conv)
            <div class="flex items-center gap-3 py-2 border-b border-gray-100 last:border-0">
                <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center text-xs font-semibold text-gray-600">
                    {{ strtoupper(substr($conv->contact->name ?? '?', 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ $conv->contact->name ?? 'Sem nome' }}</p>
                    <p class="text-xs text-gray-500">{{ $conv->lastMessage->body ?? 'Nova conversa' }}</p>
                </div>
                <span class="text-xs text-gray-400">{{ $conv->last_message_at?->diffForHumans(null, true) }}</span>
            </div>
            @empty
            <p class="text-sm text-gray-500 text-center py-8">Nenhuma conversa pendente</p>
            @endforelse
        </div>

        {{-- SLA Warning Tickets --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-gray-900">Tickets SLA em Risco</h3>
                <a href="{{ route('tickets.index') }}" class="text-sm text-primary-600 hover:text-primary-700">Ver todos</a>
            </div>
            @forelse($slaWarningTickets as $ticket)
            <div class="flex items-center gap-3 py-2 border-b border-gray-100 last:border-0">
                <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ $ticket->number }} - {{ $ticket->subject }}</p>
                    <p class="text-xs text-red-600">Vence {{ $ticket->due_at->diffForHumans() }}</p>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-500 text-center py-8">Nenhum ticket em risco</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Channel Chart (Donut)
    const channelData = @json($conversationsByChannel);
    const channelLabels = {
        'whatsapp_meta': 'WhatsApp',
        'whatsapp_evolution': 'WhatsApp (Evo)',
        'facebook': 'Facebook',
        'instagram': 'Instagram',
        'telegram': 'Telegram',
        'email': 'E-mail',
        'webchat': 'Webchat',
        'sms': 'SMS'
    };
    const channelColors = {
        'whatsapp_meta': '#25D366',
        'whatsapp_evolution': '#128C7E',
        'facebook': '#1877F2',
        'instagram': '#E4405F',
        'telegram': '#0088cc',
        'email': '#6366F1',
        'webchat': '#F59E0B',
        'sms': '#8B5CF6'
    };

    if (Object.keys(channelData).length > 0) {
        new Chart(document.getElementById('channelChart'), {
            type: 'doughnut',
            data: {
                labels: Object.keys(channelData).map(k => channelLabels[k] || k),
                datasets: [{
                    data: Object.values(channelData),
                    backgroundColor: Object.keys(channelData).map(k => channelColors[k] || '#9CA3AF'),
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: { legend: { position: 'right', labels: { padding: 16, usePointStyle: true, pointStyle: 'circle' } } }
            }
        });
    }

    // Volume Chart (Line)
    const volumeData = @json($dailyVolume);
    const labels = Object.keys(volumeData).map(d => {
        const date = new Date(d + 'T00:00:00');
        return date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });
    });

    new Chart(document.getElementById('volumeChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Conversas',
                data: Object.values(volumeData),
                borderColor: '#2563EB',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 2,
                pointRadius: 4,
                pointBackgroundColor: '#2563EB'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } },
                x: { grid: { display: false } }
            },
            plugins: { legend: { display: false } }
        }
    });
});
</script>
@endpush