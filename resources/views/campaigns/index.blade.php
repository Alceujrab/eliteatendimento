@extends('layouts.app')

@section('title', 'Campanhas')
@section('page-title', 'Campanhas')

@section('header-actions')
<a href="{{ route('campaigns.create') }}" class="bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    Nova Campanha
</a>
@endsection

@section('content')
<div class="space-y-4">
    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="stat-card">
            <p class="text-2xl font-bold text-gray-900">{{ $statusCounts->sum() }}</p>
            <p class="text-sm text-gray-500">Total</p>
        </div>
        <div class="stat-card">
            <p class="text-2xl font-bold text-blue-600">{{ $statusCounts['draft'] ?? 0 }}</p>
            <p class="text-sm text-gray-500">Rascunho</p>
        </div>
        <div class="stat-card">
            <p class="text-2xl font-bold text-green-600">{{ $statusCounts['running'] ?? 0 }}</p>
            <p class="text-sm text-gray-500">Em Execução</p>
        </div>
        <div class="stat-card">
            <p class="text-2xl font-bold text-gray-600">{{ $statusCounts['completed'] ?? 0 }}</p>
            <p class="text-sm text-gray-500">Concluídas</p>
        </div>
    </div>

    {{-- Campaigns List --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Nome</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Tipo</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Destinatários</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Entregues</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Lidos</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Data</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($campaigns as $campaign)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <a href="{{ route('campaigns.show', $campaign) }}" class="font-medium text-gray-900 hover:text-primary-600">{{ $campaign->name }}</a>
                    </td>
                    <td class="px-4 py-3">
                        <span class="badge bg-gray-100 text-gray-700 uppercase">{{ $campaign->type }}</span>
                    </td>
                    <td class="px-4 py-3"><span class="badge {{ $campaign->statusBadge }}">{{ ucfirst($campaign->status) }}</span></td>
                    <td class="px-4 py-3 text-gray-600">{{ number_format($campaign->total_recipients) }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ number_format($campaign->delivered_count) }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ number_format($campaign->read_count) }}</td>
                    <td class="px-4 py-3 text-gray-400">{{ $campaign->created_at->format('d/m/Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-12 text-center text-gray-500">Nenhuma campanha criada</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $campaigns->links() }}
</div>
@endsection