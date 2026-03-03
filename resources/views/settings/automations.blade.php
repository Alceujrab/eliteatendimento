@extends('layouts.app')

@section('title', 'Automações')
@section('page-title', 'Configurações')

@section('content')
<div class="flex gap-6">
    @include('settings._nav')

    <div class="flex-1">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Automações</h3>
                <p class="text-sm text-gray-500">Integração com n8n e fluxos automatizados</p>
            </div>
            <button onclick="document.getElementById('modalNewAutomation').classList.remove('hidden')"
                    class="bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nova Automação
            </button>
        </div>

        <div class="space-y-3">
            @forelse($automations as $auto)
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900">{{ $auto->name }}</h4>
                            <p class="text-xs text-gray-500">Trigger: {{ $auto->trigger_event }} · Tipo: {{ $auto->action_type }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <form method="POST" action="{{ route('settings.automations.toggle', $auto) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $auto->is_active ? 'bg-primary-600' : 'bg-gray-200' }}">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $auto->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('settings.automations.destroy', $auto) }}" onsubmit="return confirm('Remover?')">
                            @csrf @method('DELETE')
                            <button class="text-gray-400 hover:text-red-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                        </form>
                    </div>
                </div>
                @if($auto->description)
                <p class="mt-2 text-sm text-gray-600 ml-14">{{ $auto->description }}</p>
                @endif
                @if($auto->webhook_url)
                <p class="mt-2 text-xs text-gray-400 font-mono bg-gray-50 px-3 py-1 rounded ml-14">{{ $auto->webhook_url }}</p>
                @endif
            </div>
            @empty
            <div class="text-center py-12 text-gray-500">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <p>Nenhuma automação configurada</p>
                <p class="text-xs mt-1">Configure integrações com n8n, webhooks e chatbots</p>
            </div>
            @endforelse
        </div>

        {{-- Modal --}}
        <div id="modalNewAutomation" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl w-full max-w-lg p-6 relative">
                <button onclick="document.getElementById('modalNewAutomation').classList.add('hidden')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Nova Automação</h3>
                <form method="POST" action="{{ route('settings.automations.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                        <input type="text" name="name" required class="w-full rounded-lg border-gray-300 text-sm" placeholder="Boas-vindas automático">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                        <textarea name="description" rows="2" class="w-full rounded-lg border-gray-300 text-sm"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Evento Gatilho *</label>
                        <select name="trigger_event" required class="w-full rounded-lg border-gray-300 text-sm">
                            <option value="new_conversation">Nova Conversa</option>
                            <option value="new_lead">Novo Lead</option>
                            <option value="new_ticket">Novo Ticket</option>
                            <option value="message_received">Mensagem Recebida</option>
                            <option value="lead_stage_changed">Lead Mudou de Estágio</option>
                            <option value="ticket_status_changed">Ticket Mudou Status</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Ação *</label>
                        <select name="action_type" required class="w-full rounded-lg border-gray-300 text-sm">
                            <option value="webhook">Webhook (n8n)</option>
                            <option value="auto_reply">Resposta Automática</option>
                            <option value="assign">Atribuir Agente</option>
                            <option value="tag">Adicionar Tag</option>
                            <option value="notification">Enviar Notificação</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">URL Webhook</label>
                        <input type="url" name="webhook_url" class="w-full rounded-lg border-gray-300 text-sm" placeholder="https://n8n.example.com/webhook/...">
                    </div>
                    <div class="flex justify-end gap-3 pt-3 border-t">
                        <button type="button" onclick="document.getElementById('modalNewAutomation').classList.add('hidden')" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">Cancelar</button>
                        <button type="submit" class="bg-primary-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-primary-700">Criar Automação</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection