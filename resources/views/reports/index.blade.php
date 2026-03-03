@extends('layouts.app')

@section('title', 'Relatórios')
@section('page-title', 'Relatórios & Analytics')

@section('header-actions')
<form method="GET" class="flex items-center gap-2">
    <input type="date" name="start" value="{{ $start }}" class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
    <span class="text-sm text-gray-400">até</span>
    <input type="date" name="end" value="{{ $end }}" class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
    <button type="submit" class="bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary-700">Filtrar</button>
</form>
@endsection

@section('content')
<div class="space-y-8">
    {{-- ==================== CONVERSAS ==================== --}}
    <section>
        <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            Conversas
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="stat-card">
                <p class="text-2xl font-bold text-primary-700">{{ $conversationStats['total'] ?? 0 }}</p>
                <p class="text-sm text-gray-500">Total no Período</p>
            </div>
            <div class="stat-card">
                <p class="text-2xl font-bold text-green-600">{{ $conversationStats['resolved'] ?? 0 }}</p>
                <p class="text-sm text-gray-500">Resolvidas</p>
            </div>
            <div class="stat-card">
                <p class="text-2xl font-bold text-amber-600">{{ $conversationStats['open'] ?? 0 }}</p>
                <p class="text-sm text-gray-500">Em Aberto</p>
            </div>
            <div class="stat-card">
                <p class="text-2xl font-bold text-gray-700">{{ $conversationStats['avg_response'] ?? '—' }}</p>
                <p class="text-sm text-gray-500">Tempo Médio Resposta</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- By Channel --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Por Canal</h3>
                <canvas id="chartConvByChannel" height="200"></canvas>
            </div>
            {{-- By Day --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Volume Diário</h3>
                <canvas id="chartConvByDay" height="200"></canvas>
            </div>
        </div>
    </section>

    {{-- ==================== LEADS ==================== --}}
    <section>
        <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Leads
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <div class="stat-card">
                <p class="text-2xl font-bold text-primary-700">{{ $leadStats['total'] ?? 0 }}</p>
                <p class="text-sm text-gray-500">Total</p>
            </div>
            <div class="stat-card">
                <p class="text-2xl font-bold text-green-600">{{ $leadStats['won'] ?? 0 }}</p>
                <p class="text-sm text-gray-500">Ganhos</p>
            </div>
            <div class="stat-card">
                <p class="text-2xl font-bold text-red-500">{{ $leadStats['lost'] ?? 0 }}</p>
                <p class="text-sm text-gray-500">Perdidos</p>
            </div>
            <div class="stat-card">
                <p class="text-2xl font-bold text-amber-600">{{ $leadStats['active'] ?? 0 }}</p>
                <p class="text-sm text-gray-500">Ativos</p>
            </div>
            <div class="stat-card">
                @php
                    $convRate = ($leadStats['total'] ?? 0) > 0 ? round((($leadStats['won'] ?? 0) / $leadStats['total']) * 100, 1) : 0;
                @endphp
                <p class="text-2xl font-bold text-primary-700">{{ $convRate }}%</p>
                <p class="text-sm text-gray-500">Taxa Conversão</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- By Stage --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Funil de Vendas</h3>
                @if(isset($leadsByStage))
                <div class="space-y-3">
                    @foreach($leadsByStage as $stage)
                    @php $maxCount = $leadsByStage->max('count') ?: 1; @endphp
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-700">{{ $stage->stage }}</span>
                            <span class="font-medium">{{ $stage->count }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-3">
                            <div class="bg-primary-500 h-3 rounded-full transition-all" style="width: {{ ($stage->count / $maxCount) * 100 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            {{-- By Source --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Por Origem</h3>
                <canvas id="chartLeadsBySource" height="200"></canvas>
            </div>
        </div>
    </section>

    {{-- ==================== TICKETS ==================== --}}
    <section>
        <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
            Tickets
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="stat-card">
                <p class="text-2xl font-bold text-primary-700">{{ $ticketStats['total'] ?? 0 }}</p>
                <p class="text-sm text-gray-500">Total</p>
            </div>
            <div class="stat-card">
                <p class="text-2xl font-bold text-green-600">{{ $ticketStats['resolved'] ?? 0 }}</p>
                <p class="text-sm text-gray-500">Resolvidos</p>
            </div>
            <div class="stat-card">
                <p class="text-2xl font-bold text-red-500">{{ $ticketStats['sla_overdue'] ?? 0 }}</p>
                <p class="text-sm text-gray-500">SLA Estourado</p>
            </div>
            <div class="stat-card">
                <p class="text-2xl font-bold text-gray-700">{{ $ticketStats['avg_resolution'] ?? '—' }}</p>
                <p class="text-sm text-gray-500">Tempo Médio Resolução</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Por Categoria</h3>
            <canvas id="chartTicketsByCategory" height="160"></canvas>
        </div>
    </section>

    {{-- ==================== SATISFAÇÃO ==================== --}}
    <section>
        <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
            Satisfação do Cliente
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div class="stat-card text-center">
                @php
                    $npsScore = ($nps['score'] ?? 0);
                    $npsColor = $npsScore >= 50 ? 'text-green-600' : ($npsScore >= 0 ? 'text-amber-600' : 'text-red-600');
                @endphp
                <p class="text-4xl font-bold {{ $npsColor }}">{{ $npsScore }}</p>
                <p class="text-sm text-gray-500 mt-1">NPS Score</p>
                <div class="flex justify-center gap-4 mt-3 text-xs">
                    <span class="text-green-600">Promotores: {{ $nps['promoters'] ?? 0 }}%</span>
                    <span class="text-gray-500">Neutros: {{ $nps['passives'] ?? 0 }}%</span>
                    <span class="text-red-500">Detratores: {{ $nps['detractors'] ?? 0 }}%</span>
                </div>
            </div>
            <div class="stat-card text-center">
                <p class="text-4xl font-bold text-amber-500">{{ number_format($csat['average'] ?? 0, 1) }}</p>
                <p class="text-sm text-gray-500 mt-1">CSAT Médio</p>
                <p class="text-xs text-gray-400 mt-1">de 5.0</p>
            </div>
            <div class="stat-card text-center">
                <p class="text-4xl font-bold text-primary-700">{{ $csat['total_responses'] ?? 0 }}</p>
                <p class="text-sm text-gray-500 mt-1">Pesquisas Respondidas</p>
            </div>
        </div>
    </section>

    {{-- ==================== DESEMPENHO DOS AGENTES ==================== --}}
    <section>
        <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            Desempenho dos Agentes
        </h2>
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Agente</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Conversas</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Leads</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Tickets</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Tempo Médio</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">NPS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($agentPerformance ?? [] as $agent)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center text-xs font-medium">
                                    {{ substr($agent['name'] ?? '', 0, 2) }}
                                </div>
                                <span class="text-sm font-medium text-gray-900">{{ $agent['name'] ?? '—' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center text-sm text-gray-600">{{ $agent['conversations'] ?? 0 }}</td>
                        <td class="px-6 py-4 text-center text-sm text-gray-600">{{ $agent['leads'] ?? 0 }}</td>
                        <td class="px-6 py-4 text-center text-sm text-gray-600">{{ $agent['tickets'] ?? 0 }}</td>
                        <td class="px-6 py-4 text-center text-sm text-gray-600">{{ $agent['avg_response_time'] ?? '—' }}</td>
                        <td class="px-6 py-4 text-center text-sm font-medium {{ ($agent['nps'] ?? 0) >= 50 ? 'text-green-600' : (($agent['nps'] ?? 0) >= 0 ? 'text-amber-600' : 'text-red-600') }}">
                            {{ $agent['nps'] ?? '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">Nenhum dado de agente disponível</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- ==================== CAMPANHAS ==================== --}}
    <section>
        <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
            Campanhas
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="stat-card">
                <p class="text-2xl font-bold text-primary-700">{{ $campaignStats['total'] ?? 0 }}</p>
                <p class="text-sm text-gray-500">Total</p>
            </div>
            <div class="stat-card">
                <p class="text-2xl font-bold text-green-600">{{ $campaignStats['sent'] ?? 0 }}</p>
                <p class="text-sm text-gray-500">Enviadas</p>
            </div>
            <div class="stat-card">
                <p class="text-2xl font-bold text-blue-600">{{ number_format($campaignStats['delivery_rate'] ?? 0, 1) }}%</p>
                <p class="text-sm text-gray-500">Taxa Entrega</p>
            </div>
            <div class="stat-card">
                <p class="text-2xl font-bold text-amber-600">{{ number_format($campaignStats['read_rate'] ?? 0, 1) }}%</p>
                <p class="text-sm text-gray-500">Taxa Leitura</p>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Conversations by Channel
    const convByChannelData = @json($conversationsByChannel ?? []);
    if (document.getElementById('chartConvByChannel') && convByChannelData.length) {
        const channelColors = { whatsapp: '#25D366', instagram: '#E4405F', facebook: '#1877F2', telegram: '#0088cc', email: '#6B7280', webchat: '#2563EB', sms: '#8B5CF6', phone: '#F59E0B' };
        new Chart(document.getElementById('chartConvByChannel'), {
            type: 'doughnut',
            data: {
                labels: convByChannelData.map(c => c.channel),
                datasets: [{ data: convByChannelData.map(c => c.count), backgroundColor: convByChannelData.map(c => channelColors[c.channel] || '#9CA3AF') }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } } }
        });
    }

    // Conversations by Day
    const convByDayData = @json($conversationsByDay ?? []);
    if (document.getElementById('chartConvByDay') && convByDayData.length) {
        new Chart(document.getElementById('chartConvByDay'), {
            type: 'line',
            data: {
                labels: convByDayData.map(d => d.date),
                datasets: [{ label: 'Conversas', data: convByDayData.map(d => d.count), borderColor: '#2563EB', backgroundColor: 'rgba(37,99,235,0.1)', fill: true, tension: 0.4 }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    }

    // Leads by Source
    const leadsBySourceData = @json($leadsBySource ?? []);
    if (document.getElementById('chartLeadsBySource') && leadsBySourceData.length) {
        new Chart(document.getElementById('chartLeadsBySource'), {
            type: 'bar',
            data: {
                labels: leadsBySourceData.map(s => s.source || 'Não informado'),
                datasets: [{ label: 'Leads', data: leadsBySourceData.map(s => s.count), backgroundColor: '#2563EB', borderRadius: 6 }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    }

    // Tickets by Category
    const ticketsByCatData = @json($ticketsByCategory ?? []);
    if (document.getElementById('chartTicketsByCategory') && ticketsByCatData.length) {
        new Chart(document.getElementById('chartTicketsByCategory'), {
            type: 'bar',
            data: {
                labels: ticketsByCatData.map(c => c.category || 'Não informado'),
                datasets: [{ label: 'Tickets', data: ticketsByCatData.map(c => c.count), backgroundColor: '#F59E0B', borderRadius: 6 }]
            },
            options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true } } }
        });
    }
});
</script>
@endpush
@endsection