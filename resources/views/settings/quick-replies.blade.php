@extends('layouts.app')

@section('title', 'Respostas Rápidas')
@section('page-title', 'Configurações')

@section('content')
<div class="flex gap-6">
    @include('settings._nav')

    <div class="flex-1">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Respostas Rápidas</h3>
                <p class="text-sm text-gray-500">Atalhos de mensagem para agilizar o atendimento</p>
            </div>
            <button onclick="document.getElementById('modalNewReply').classList.remove('hidden')"
                    class="bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nova Resposta
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($quickReplies as $reply)
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <span class="text-xs font-mono bg-primary-50 text-primary-700 px-2 py-0.5 rounded">/{{ $reply->shortcut }}</span>
                        @if($reply->category)
                        <span class="text-xs text-gray-400 ml-2">{{ $reply->category }}</span>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('settings.quick-replies.destroy', $reply) }}" onsubmit="return confirm('Remover?')">
                        @csrf @method('DELETE')
                        <button class="text-gray-300 hover:text-red-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </form>
                </div>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $reply->message }}</p>
            </div>
            @empty
            <div class="col-span-full text-center py-8 text-gray-500">Nenhuma resposta rápida cadastrada</div>
            @endforelse
        </div>

        {{-- Modal --}}
        <div id="modalNewReply" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl w-full max-w-md p-6 relative">
                <button onclick="document.getElementById('modalNewReply').classList.add('hidden')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Nova Resposta Rápida</h3>
                <form method="POST" action="{{ route('settings.quick-replies.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Atalho *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-sm text-gray-400">/</span>
                            <input type="text" name="shortcut" required class="w-full rounded-lg border-gray-300 text-sm pl-7" placeholder="saudacao">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                        <input type="text" name="category" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Geral, Vendas, Suporte...">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mensagem *</label>
                        <textarea name="message" rows="4" required class="w-full rounded-lg border-gray-300 text-sm"
                                  placeholder="Olá! Obrigado por entrar em contato..."></textarea>
                        <p class="text-xs text-gray-400 mt-1">Variáveis: {nome}, {empresa}, {veiculo}</p>
                    </div>
                    <div class="flex justify-end gap-3 pt-3 border-t">
                        <button type="button" onclick="document.getElementById('modalNewReply').classList.add('hidden')" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">Cancelar</button>
                        <button type="submit" class="bg-primary-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-primary-700">Criar Resposta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection