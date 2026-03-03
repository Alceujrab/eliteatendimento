@extends('layouts.app')

@section('title', 'Políticas SLA')
@section('page-title', 'Configurações')

@section('content')
<div class="flex gap-6">
    @include('settings._nav')

    <div class="flex-1">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Políticas de SLA</h3>
                <p class="text-sm text-gray-500">Defina tempos máximos de resposta e resolução por prioridade</p>
            </div>
            <button onclick="document.getElementById('modalNewSla').classList.remove('hidden')"
                    class="bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nova Política
            </button>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Prioridade</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">1ª Resposta</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Resolução</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($slaPolicies as $sla)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $sla->name }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="badge {{ $sla->priority === 'urgent' ? 'bg-red-100 text-red-700' : ($sla->priority === 'high' ? 'bg-orange-100 text-orange-700' : ($sla->priority === 'medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700')) }}">
                                {{ ucfirst($sla->priority) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center text-sm text-gray-600">{{ $sla->first_response_time }}min</td>
                        <td class="px-6 py-4 text-center text-sm text-gray-600">{{ $sla->resolution_time }}min</td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-xs {{ $sla->is_active ? 'text-green-600' : 'text-gray-400' }}">{{ $sla->is_active ? 'Ativo' : 'Inativo' }}</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <form method="POST" action="{{ route('settings.sla.destroy', $sla) }}" class="inline" onsubmit="return confirm('Remover?')">
                                @csrf @method('DELETE')
                                <button class="text-gray-400 hover:text-red-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">Nenhuma política configurada</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Modal --}}
        <div id="modalNewSla" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl w-full max-w-md p-6 relative">
                <button onclick="document.getElementById('modalNewSla').classList.add('hidden')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Nova Política SLA</h3>
                <form method="POST" action="{{ route('settings.sla.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                        <input type="text" name="name" required class="w-full rounded-lg border-gray-300 text-sm" placeholder="SLA Urgente">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prioridade *</label>
                        <select name="priority" required class="w-full rounded-lg border-gray-300 text-sm">
                            <option value="low">Baixa</option>
                            <option value="medium" selected>Média</option>
                            <option value="high">Alta</option>
                            <option value="urgent">Urgente</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">1ª Resposta (minutos) *</label>
                        <input type="number" name="first_response_time" required min="1" class="w-full rounded-lg border-gray-300 text-sm" placeholder="30">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Resolução (minutos) *</label>
                        <input type="number" name="resolution_time" required min="1" class="w-full rounded-lg border-gray-300 text-sm" placeholder="480">
                    </div>
                    <div class="flex justify-end gap-3 pt-3 border-t">
                        <button type="button" onclick="document.getElementById('modalNewSla').classList.add('hidden')" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">Cancelar</button>
                        <button type="submit" class="bg-primary-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-primary-700">Criar Política</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection