@extends('layouts.app')

@section('title', 'Configurações')
@section('page-title', 'Configurações')

@section('content')
<div class="flex gap-6">
    @include('settings._nav')

    <div class="flex-1 max-w-3xl">
        <form method="POST" action="{{ route('settings.updateTenant') }}" class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
            @csrf @method('PUT')

            <h3 class="text-lg font-semibold text-gray-900">Dados da Empresa</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome da Empresa *</label>
                    <input type="text" name="name" value="{{ old('name', $tenant->name) }}" required
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CNPJ</label>
                    <input type="text" name="document" value="{{ old('document', $tenant->document) }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                    <input type="text" name="phone" value="{{ old('phone', $tenant->phone) }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                    <input type="email" name="email" value="{{ old('email', $tenant->email) }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Website</label>
                    <input type="url" name="website" value="{{ old('website', $tenant->website) }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                </div>
            </div>

            <h4 class="text-sm font-semibold text-gray-900 pt-3 border-t">Endereço</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Endereço</label>
                    <input type="text" name="address" value="{{ old('address', $tenant->address) }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cidade / UF</label>
                    <input type="text" name="city" value="{{ old('city', $tenant->city) }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                </div>
            </div>

            <h4 class="text-sm font-semibold text-gray-900 pt-3 border-t">Horário de Atendimento</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Abre às</label>
                    <input type="time" name="business_hours_start" value="{{ old('business_hours_start', $tenant->business_hours_start ?? '08:00') }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha às</label>
                    <input type="time" name="business_hours_end" value="{{ old('business_hours_end', $tenant->business_hours_end ?? '18:00') }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t">
                <button type="submit" class="bg-primary-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors">Salvar Configurações</button>
            </div>
        </form>
    </div>
</div>
@endsection