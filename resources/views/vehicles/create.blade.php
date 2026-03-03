@extends('layouts.app')

@section('title', 'Novo Veículo')
@section('page-title', 'Novo Veículo')

@section('content')
<div class="max-w-4xl mx-auto">
    <form method="POST" action="{{ route('vehicles.store') }}" enctype="multipart/form-data" class="bg-white rounded-xl border border-gray-200 p-6 space-y-6">
        @csrf

        <h3 class="text-lg font-semibold text-gray-900 border-b pb-3">Informações do Veículo</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Marca *</label>
                <input type="text" name="brand" value="{{ old('brand') }}" required
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                       placeholder="Toyota">
                @error('brand') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Modelo *</label>
                <input type="text" name="model" value="{{ old('model') }}" required
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                       placeholder="Corolla XEi">
                @error('model') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Versão</label>
                <input type="text" name="version" value="{{ old('version') }}"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                       placeholder="2.0 Dynamic">
                @error('version') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ano Fab. *</label>
                <input type="number" name="year_manufacture" value="{{ old('year_manufacture') }}" required min="1990" max="{{ date('Y') + 1 }}"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                @error('year_manufacture') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ano Modelo *</label>
                <input type="number" name="year_model" value="{{ old('year_model') }}" required min="1990" max="{{ date('Y') + 2 }}"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                @error('year_model') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cor *</label>
                <input type="text" name="color" value="{{ old('color') }}" required
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                       placeholder="Prata">
                @error('color') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Placa</label>
                <input type="text" name="plate" value="{{ old('plate') }}"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500 uppercase"
                       placeholder="ABC1D23" maxlength="7">
                @error('plate') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Combustível *</label>
                <select name="fuel" required class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">Selecione</option>
                    <option value="flex" {{ old('fuel') === 'flex' ? 'selected' : '' }}>Flex</option>
                    <option value="gasoline" {{ old('fuel') === 'gasoline' ? 'selected' : '' }}>Gasolina</option>
                    <option value="ethanol" {{ old('fuel') === 'ethanol' ? 'selected' : '' }}>Etanol</option>
                    <option value="diesel" {{ old('fuel') === 'diesel' ? 'selected' : '' }}>Diesel</option>
                    <option value="electric" {{ old('fuel') === 'electric' ? 'selected' : '' }}>Elétrico</option>
                    <option value="hybrid" {{ old('fuel') === 'hybrid' ? 'selected' : '' }}>Híbrido</option>
                </select>
                @error('fuel') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Câmbio *</label>
                <select name="transmission" required class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">Selecione</option>
                    <option value="manual" {{ old('transmission') === 'manual' ? 'selected' : '' }}>Manual</option>
                    <option value="automatic" {{ old('transmission') === 'automatic' ? 'selected' : '' }}>Automático</option>
                    <option value="cvt" {{ old('transmission') === 'cvt' ? 'selected' : '' }}>CVT</option>
                    <option value="automated" {{ old('transmission') === 'automated' ? 'selected' : '' }}>Automatizado</option>
                </select>
                @error('transmission') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Quilometragem *</label>
                <input type="number" name="mileage" value="{{ old('mileage') }}" required min="0"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                       placeholder="45000">
                @error('mileage') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <h3 class="text-lg font-semibold text-gray-900 border-b pb-3 pt-2">Preços</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Preço de Venda *</label>
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-sm text-gray-400">R$</span>
                    <input type="number" name="price" value="{{ old('price') }}" required min="0" step="0.01"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500 pl-10">
                </div>
                @error('price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Preço FIPE</label>
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-sm text-gray-400">R$</span>
                    <input type="number" name="fipe_price" value="{{ old('fipe_price') }}" min="0" step="0.01"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500 pl-10">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Custo de Aquisição</label>
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-sm text-gray-400">R$</span>
                    <input type="number" name="purchase_price" value="{{ old('purchase_price') }}" min="0" step="0.01"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500 pl-10">
                </div>
            </div>
        </div>

        <h3 class="text-lg font-semibold text-gray-900 border-b pb-3 pt-2">Fotos & Detalhes</h3>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Fotos do Veículo</label>
            <div x-data="{ previews: [] }" class="space-y-3">
                <div class="flex items-center justify-center w-full">
                    <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <svg class="w-8 h-8 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            <p class="text-sm text-gray-500">Clique ou arraste fotos aqui</p>
                            <p class="text-xs text-gray-400">PNG, JPG até 5MB cada</p>
                        </div>
                        <input type="file" name="photos[]" multiple accept="image/*" class="hidden"
                               @change="previews = Array.from($event.target.files).map(f => URL.createObjectURL(f))">
                    </label>
                </div>
                <div x-show="previews.length > 0" class="flex flex-wrap gap-2">
                    <template x-for="(src, i) in previews" :key="i">
                        <img :src="src" class="w-20 h-20 rounded-lg object-cover border">
                    </template>
                </div>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Opcionais / Características</label>
            <textarea name="features" rows="3"
                      class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                      placeholder="Ar-condicionado, Direção elétrica, Central multimídia, Câmera de ré...">{{ old('features') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Observações Internas</label>
            <textarea name="notes" rows="2"
                      class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                      placeholder="Notas internas sobre o veículo...">{{ old('notes') }}</textarea>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t">
            <a href="{{ route('vehicles.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="bg-primary-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors">Salvar Veículo</button>
        </div>
    </form>
</div>
@endsection