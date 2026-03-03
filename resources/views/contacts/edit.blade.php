@extends('layouts.app')

@section('title', 'Editar ' . $contact->name)
@section('page-title', 'Editar Contato')

@section('content')
<div class="max-w-2xl mx-auto">
    <form method="POST" action="{{ route('contacts.update', $contact) }}" class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo *</label>
                <input type="text" name="name" value="{{ old('name', $contact->name) }}" required
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Telefone *</label>
                <input type="text" name="phone" value="{{ old('phone', $contact->phone) }}" required
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                <input type="email" name="email" value="{{ old('email', $contact->email) }}"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">CPF/CNPJ</label>
                <input type="text" name="document" value="{{ old('document', $contact->document) }}"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Empresa</label>
                <input type="text" name="company" value="{{ old('company', $contact->company) }}"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cidade</label>
                <input type="text" name="city" value="{{ old('city', $contact->city) }}"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                <input type="text" name="state" value="{{ old('state', $contact->state) }}"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500" maxlength="2">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tags</label>
            <input type="text" name="tags" value="{{ old('tags', is_array($contact->tags) ? implode(', ', $contact->tags) : $contact->tags) }}"
                   class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                   placeholder="Separadas por vírgula">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
            <textarea name="notes" rows="3"
                      class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">{{ old('notes', $contact->notes) }}</textarea>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t">
            <a href="{{ route('contacts.show', $contact) }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="bg-primary-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors">Salvar Alterações</button>
        </div>
    </form>
</div>
@endsection