@extends('layouts.app')

@section('title', 'Novo Ticket')
@section('page-title', 'Novo Ticket')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <form method="POST" action="{{ route('tickets.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Assunto</label>
                <input type="text" name="subject" value="{{ old('subject') }}" required
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                       placeholder="Resumo do problema">
                @error('subject') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contato</label>
                    <select name="contact_id" required class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="">Selecione...</option>
                        @foreach(\App\Models\Contact::where('tenant_id', auth()->user()->tenant_id)->orderBy('name')->get() as $contact)
                            <option value="{{ $contact->id }}">{{ $contact->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Responsável</label>
                    <select name="assigned_to" class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="">Não atribuído</option>
                        @foreach(\App\Models\User::where('tenant_id', auth()->user()->tenant_id)->where('is_active', true)->get() as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                    <select name="category" required class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="duvida">Dúvida</option>
                        <option value="reclamacao">Reclamação</option>
                        <option value="solicitacao">Solicitação</option>
                        <option value="pos_venda">Pós-venda</option>
                        <option value="financeiro">Financeiro</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Prioridade</label>
                    <select name="priority" required class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="low">Baixa</option>
                        <option value="medium" selected>Média</option>
                        <option value="high">Alta</option>
                        <option value="urgent">Urgente</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                <textarea name="body" rows="5" required
                          class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                          placeholder="Descreva o problema em detalhes...">{{ old('body') }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t">
                <a href="{{ route('tickets.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">Cancelar</a>
                <button type="submit" class="bg-primary-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors">
                    Criar Ticket
                </button>
            </div>
        </form>
    </div>
</div>
@endsection