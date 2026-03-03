@extends('layouts.app')

@section('title', 'Novo Contato')
@section('page-title', 'Novo Contato')

@section('content')
<div class="max-w-2xl mx-auto">
    <form method="POST" action="{{ route('contacts.store') }}" class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo *</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                       placeholder="Nome completo do contato">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Telefone *</label>
                <input type="text" name="phone" value="{{ old('phone') }}" required
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                       placeholder="(11) 99999-9999">
                @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                       placeholder="contato@email.com">
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">CPF/CNPJ</label>
                <input type="text" name="document" value="{{ old('document') }}"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                       placeholder="000.000.000-00">
                @error('document') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Empresa</label>
                <input type="text" name="company" value="{{ old('company') }}"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                       placeholder="Nome da empresa">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cidade</label>
                <input type="text" name="city" value="{{ old('city') }}"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                <input type="text" name="state" value="{{ old('state') }}"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500" maxlength="2"
                       placeholder="SP">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tags</label>
            <input type="text" name="tags" value="{{ old('tags') }}"
                   class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                   placeholder="Separadas por vírgula: VIP, Recorrente, Indicação">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
            <textarea name="notes" rows="3"
                      class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                      placeholder="Notas internas sobre o contato...">{{ old('notes') }}</textarea>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t">
            <a href="{{ route('contacts.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="bg-primary-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors">Salvar Contato</button>
        </div>
    </form>
</div>
@endsection