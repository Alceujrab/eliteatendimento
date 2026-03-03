@extends('layouts.app')

@section('title', 'Veículos')
@section('page-title', 'Veículos')

@section('header-actions')
<a href="{{ route('vehicles.create') }}" class="bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    Novo Veículo
</a>
@endsection

@section('content')
<div class="space-y-4">
    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por marca, modelo ou placa..."
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
            </div>
            <div>
                <select name="status" class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">Todos Status</option>
                    <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Disponível</option>
                    <option value="reserved" {{ request('status') === 'reserved' ? 'selected' : '' }}>Reservado</option>
                    <option value="sold" {{ request('status') === 'sold' ? 'selected' : '' }}>Vendido</option>
                </select>
            </div>
            <div>
                <select name="brand" class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">Todas Marcas</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand }}" {{ request('brand') === $brand ? 'selected' : '' }}>{{ $brand }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200">Filtrar</button>
        </form>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="stat-card">
            <p class="text-2xl font-bold text-green-600">{{ $statusCounts['available'] ?? 0 }}</p>
            <p class="text-sm text-gray-500">Disponíveis</p>
        </div>
        <div class="stat-card">
            <p class="text-2xl font-bold text-amber-600">{{ $statusCounts['reserved'] ?? 0 }}</p>
            <p class="text-sm text-gray-500">Reservados</p>
        </div>
        <div class="stat-card">
            <p class="text-2xl font-bold text-gray-600">{{ $statusCounts['sold'] ?? 0 }}</p>
            <p class="text-sm text-gray-500">Vendidos</p>
        </div>
    </div>

    {{-- Vehicle Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($vehicles as $vehicle)
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
            {{-- Photo --}}
            <div class="h-48 bg-gray-100 relative">
                @if($vehicle->mainPhoto)
                    <img src="{{ Storage::url($vehicle->mainPhoto) }}" alt="{{ $vehicle->fullName }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-300">
                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10m10 0H3m10 0h2m4 0h1a1 1 0 001-1v-4l-3-5h-4v10z"/></svg>
                    </div>
                @endif
                <div class="absolute top-2 right-2">
                    <span class="badge {{ $vehicle->status === 'available' ? 'bg-green-100 text-green-700' : ($vehicle->status === 'reserved' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-700') }}">
                        {{ $vehicle->status === 'available' ? 'Disponível' : ($vehicle->status === 'reserved' ? 'Reservado' : 'Vendido') }}
                    </span>
                </div>
            </div>

            {{-- Info --}}
            <div class="p-4">
                <a href="{{ route('vehicles.show', $vehicle) }}" class="text-base font-semibold text-gray-900 hover:text-primary-600">
                    {{ $vehicle->fullName }}
                </a>
                <p class="text-sm text-gray-500 mt-1">{{ $vehicle->yearDisplay }} · {{ $vehicle->fuelLabel }} · {{ $vehicle->transmissionLabel }}</p>

                <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
                    <div>
                        <p class="text-lg font-bold text-primary-700">{{ $vehicle->formattedPrice }}</p>
                        @if($vehicle->fipe_price)
                            <p class="text-xs text-gray-400">FIPE: R$ {{ number_format($vehicle->fipe_price, 0, ',', '.') }}</p>
                        @endif
                    </div>
                    <p class="text-xs text-gray-400">{{ $vehicle->formattedMileage }}</p>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12 text-gray-500">Nenhum veículo encontrado</div>
        @endforelse
    </div>

    {{ $vehicles->links() }}
</div>
@endsection