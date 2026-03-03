@extends('layouts.app')

@section('title', 'Canais')
@section('page-title', 'Configurações')

@section('content')
<div class="flex gap-6">
    @include('settings._nav')

    <div class="flex-1">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Canais de Atendimento</h3>
            <button onclick="document.getElementById('modalNewChannel').classList.remove('hidden')"
                    class="bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Novo Canal
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($channels as $channel)
            <div class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-sm transition-shadow">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center text-white text-lg" style="background-color: {{ $channel->color ?? '#6B7280' }}">
                            {!! $channel->icon !!}
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900">{{ $channel->name }}</h4>
                            <p class="text-xs text-gray-500 capitalize">{{ $channel->type }}</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1 text-xs {{ $channel->is_active ? 'text-green-600' : 'text-gray-400' }}">
                        <span class="w-2 h-2 rounded-full {{ $channel->is_active ? 'bg-green-500' : 'bg-gray-300' }}"></span>
                        {{ $channel->is_active ? 'Ativo' : 'Inativo' }}
                    </span>
                </div>
                @if($channel->identifier)
                <p class="mt-3 text-xs text-gray-400 font-mono bg-gray-50 px-2 py-1 rounded">{{ $channel->identifier }}</p>
                @endif
                <div class="mt-3 flex items-center gap-2">
                    <form method="POST" action="{{ route('settings.channels.destroy', $channel) }}" onsubmit="return confirm('Desativar este canal?')">
                        @csrf @method('DELETE')
                        <button class="text-xs text-red-500 hover:text-red-700">Remover</button>
                    </form>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-8 text-gray-500">Nenhum canal configurado</div>
            @endforelse
        </div>

        {{-- Modal --}}
        <div id="modalNewChannel" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl w-full max-w-lg p-6 relative">
                <button onclick="document.getElementById('modalNewChannel').classList.add('hidden')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Novo Canal</h3>
                <form method="POST" action="{{ route('settings.channels.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                        <input type="text" name="name" required class="w-full rounded-lg border-gray-300 text-sm" placeholder="WhatsApp Principal">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                        <select name="type" required class="w-full rounded-lg border-gray-300 text-sm">
                            <option value="whatsapp">WhatsApp</option>
                            <option value="instagram">Instagram</option>
                            <option value="facebook">Facebook Messenger</option>
                            <option value="telegram">Telegram</option>
                            <option value="email">E-mail</option>
                            <option value="webchat">Webchat</option>
                            <option value="sms">SMS</option>
                            <option value="phone">Telefone</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Identificador</label>
                        <input type="text" name="identifier" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Número, e-mail ou URL">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">URL da API (Evolution/etc)</label>
                        <input type="url" name="api_url" class="w-full rounded-lg border-gray-300 text-sm" placeholder="https://api.evolution.example.com">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">API Key</label>
                        <input type="text" name="api_key" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Chave de autenticação">
                    </div>
                    <div class="flex justify-end gap-3 pt-3 border-t">
                        <button type="button" onclick="document.getElementById('modalNewChannel').classList.add('hidden')" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">Cancelar</button>
                        <button type="submit" class="bg-primary-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-primary-700">Criar Canal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection