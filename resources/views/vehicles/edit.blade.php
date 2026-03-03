@extends('layouts.app')

@section('title', 'Editar ' . $vehicle->fullName)
@section('page-title', 'Editar Veículo')

@section('content')
<div class="max-w-4xl mx-auto">
    <form method="POST" action="{{ route('vehicles.update', $vehicle) }}" enctype="multipart/form-data" class="bg-white rounded-xl border border-gray-200 p-6 space-y-6">
        @csrf @method('PUT')

        <h3 class="text-lg font-semibold text-gray-900 border-b pb-3">Informações do Veículo</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Marca *</label>
                <input type="text" name="brand" value="{{ old('brand', $vehicle->brand) }}" required
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                @error('brand') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Modelo *</label>
                <input type="text" name="model" value="{{ old('model', $vehicle->model) }}" required
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                @error('model') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Versão</label>
                <input type="text" name="version" value="{{ old('version', $vehicle->version) }}"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ano Fab. *</label>
                <input type="number" name="year_manufacture" value="{{ old('year_manufacture', $vehicle->year_manufacture) }}" required
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                @error('year_manufacture') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ano Modelo *</label>
                <input type="number" name="year_model" value="{{ old('year_model', $vehicle->year_model) }}" required
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                @error('year_model') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cor *</label>
                <input type="text" name="color" value="{{ old('color', $vehicle->color) }}" required
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                @error('color') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Placa</label>
                <input type="text" name="plate" value="{{ old('plate', $vehicle->plate) }}"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500 uppercase" maxlength="7">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Combustível *</label>
                <select name="fuel" required class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="flex" {{ old('fuel', $vehicle->fuel) === 'flex' ? 'selected' : '' }}>Flex</option>
                    <option value="gasoline" {{ old('fuel', $vehicle->fuel) === 'gasoline' ? 'selected' : '' }}>Gasolina</option>
                    <option value="ethanol" {{ old('fuel', $vehicle->fuel) === 'ethanol' ? 'selected' : '' }}>Etanol</option>
                    <option value="diesel" {{ old('fuel', $vehicle->fuel) === 'diesel' ? 'selected' : '' }}>Diesel</option>
                    <option value="electric" {{ old('fuel', $vehicle->fuel) === 'electric' ? 'selected' : '' }}>Elétrico</option>
                    <option value="hybrid" {{ old('fuel', $vehicle->fuel) === 'hybrid' ? 'selected' : '' }}>Híbrido</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Câmbio *</label>
                <select name="transmission" required class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="manual" {{ old('transmission', $vehicle->transmission) === 'manual' ? 'selected' : '' }}>Manual</option>
                    <option value="automatic" {{ old('transmission', $vehicle->transmission) === 'automatic' ? 'selected' : '' }}>Automático</option>
                    <option value="cvt" {{ old('transmission', $vehicle->transmission) === 'cvt' ? 'selected' : '' }}>CVT</option>
                    <option value="automated" {{ old('transmission', $vehicle->transmission) === 'automated' ? 'selected' : '' }}>Automatizado</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Quilometragem *</label>
                <input type="number" name="mileage" value="{{ old('mileage', $vehicle->mileage) }}" required min="0"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
            </div>
        </div>

        <h3 class="text-lg font-semibold text-gray-900 border-b pb-3 pt-2">Preços</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Preço de Venda *</label>
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-sm text-gray-400">R$</span>
                    <input type="number" name="price" value="{{ old('price', $vehicle->price) }}" required min="0" step="0.01"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500 pl-10">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Preço FIPE</label>
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-sm text-gray-400">R$</span>
                    <input type="number" name="fipe_price" value="{{ old('fipe_price', $vehicle->fipe_price) }}" min="0" step="0.01"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500 pl-10">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Custo de Aquisição</label>
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-sm text-gray-400">R$</span>
                    <input type="number" name="purchase_price" value="{{ old('purchase_price', $vehicle->purchase_price) }}" min="0" step="0.01"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500 pl-10">
                </div>
            </div>
        </div>

        <h3 class="text-lg font-semibold text-gray-900 border-b pb-3 pt-2">Fotos</h3>

        <div>
            @php $photos = $vehicle->photos ?? []; @endphp
            @if(count($photos) > 0)
            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-2">Fotos atuais:</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($photos as $photo)
                    <div class="relative group">
                        <img src="{{ Storage::url($photo) }}" class="w-20 h-20 rounded-lg object-cover border">
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            <label class="block text-sm font-medium text-gray-700 mb-1">Adicionar novas fotos</label>
            <input type="file" name="photos[]" multiple accept="image/*"
                   class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Opcionais / Características</label>
            <textarea name="features" rows="3"
                      class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">{{ old('features', is_array($vehicle->features) ? implode(', ', $vehicle->features) : $vehicle->features) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Observações Internas</label>
            <textarea name="notes" rows="2"
                      class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">{{ old('notes', $vehicle->notes) }}</textarea>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t">
            <a href="{{ route('vehicles.show', $vehicle) }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="bg-primary-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors">Salvar Alterações</button>
        </div>
    </form>
</div>
@endsection