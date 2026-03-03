@extends('layouts.app')

@section('title', 'Nova Campanha')
@section('page-title', 'Nova Campanha')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <form method="POST" action="{{ route('campaigns.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome da Campanha</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                       placeholder="Ex: Promoção Seminovos Janeiro">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                    <select name="type" required class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="whatsapp">WhatsApp</option>
                        <option value="sms">SMS</option>
                        <option value="email">E-mail</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Agendar Para</label>
                    <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mensagem</label>
                <textarea name="message_template" rows="6" required
                          class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                          placeholder="Use {nome} para personalizar com o nome do contato...">{{ old('message_template') }}</textarea>
                <p class="text-xs text-gray-400 mt-1">Variáveis: {nome}, {telefone}, {email}</p>
            </div>

            <div class="bg-blue-50 rounded-lg p-4">
                <p class="text-sm text-blue-800">
                    <strong>{{ $contactCount }}</strong> contatos disponíveis para envio.
                    Os filtros de audiência podem ser refinados após criar a campanha.
                </p>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t">
                <a href="{{ route('campaigns.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700">Cancelar</a>
                <button type="submit" class="bg-primary-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-primary-700">
                    Criar Campanha
                </button>
            </div>
        </form>
    </div>
</div>
@endsection