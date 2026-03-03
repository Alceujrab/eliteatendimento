@extends('layouts.app')

@section('title', $vehicle->fullName)
@section('page-title', $vehicle->fullName)

@section('header-actions')
<div class="flex items-center gap-2">
    <a href="{{ route('vehicles.edit', $vehicle) }}" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 flex items-center gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        Editar
    </a>
    @if($vehicle->status === 'available')
    <form method="POST" action="{{ route('vehicles.updateStatus', $vehicle) }}" class="inline">
        @csrf @method('PATCH')
        <input type="hidden" name="status" value="reserved">
        <button type="submit" class="bg-amber-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-amber-600">Reservar</button>
    </form>
    <form method="POST" action="{{ route('vehicles.updateStatus', $vehicle) }}" class="inline">
        @csrf @method('PATCH')
        <input type="hidden" name="status" value="sold">
        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700">Marcar Vendido</button>
    </form>
    @elseif($vehicle->status === 'reserved')
    <form method="POST" action="{{ route('vehicles.updateStatus', $vehicle) }}" class="inline">
        @csrf @method('PATCH')
        <input type="hidden" name="status" value="available">
        <button type="submit" class="bg-gray-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-600">Disponibilizar</button>
    </form>
    <form method="POST" action="{{ route('vehicles.updateStatus', $vehicle) }}" class="inline">
        @csrf @method('PATCH')
        <input type="hidden" name="status" value="sold">
        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700">Marcar Vendido</button>
    </form>
    @endif
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Main Content --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Photo Gallery --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden" x-data="{ activePhoto: 0 }">
            @php $photos = $vehicle->photos ?? []; @endphp
            @if(count($photos) > 0)
            <div class="relative h-80 bg-gray-100">
                <template x-for="(photo, i) in {{ json_encode($photos) }}" :key="i">
                    <img x-show="activePhoto === i" :src="'/storage/' + photo" class="w-full h-full object-cover" x-transition>
                </template>
                <button @click="activePhoto = (activePhoto - 1 + {{ count($photos) }}) % {{ count($photos) }}" class="absolute left-2 top-1/2 -translate-y-1/2 bg-black/50 text-white rounded-full w-10 h-10 flex items-center justify-center hover:bg-black/70">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button @click="activePhoto = (activePhoto + 1) % {{ count($photos) }}" class="absolute right-2 top-1/2 -translate-y-1/2 bg-black/50 text-white rounded-full w-10 h-10 flex items-center justify-center hover:bg-black/70">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div class="absolute bottom-2 left-1/2 -translate-x-1/2 flex gap-1">
                    @for($i = 0; $i < count($photos); $i++)
                    <button @click="activePhoto = {{ $i }}" :class="activePhoto === {{ $i }} ? 'bg-white' : 'bg-white/50'" class="w-2 h-2 rounded-full"></button>
                    @endfor
                </div>
            </div>
            <div class="p-3 flex gap-2 overflow-x-auto">
                @foreach($photos as $i => $photo)
                <img @click="activePhoto = {{ $i }}" src="{{ Storage::url($photo) }}" class="w-16 h-16 rounded-lg object-cover cursor-pointer border-2 transition-colors" :class="activePhoto === {{ $i }} ? 'border-primary-500' : 'border-transparent'">
                @endforeach
            </div>
            @else
            <div class="h-64 flex items-center justify-center bg-gray-50 text-gray-300">
                <div class="text-center">
                    <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <p class="text-sm mt-2">Sem fotos cadastradas</p>
                </div>
            </div>
            @endif
        </div>

        {{-- Specs --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Especificações</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500">Ano</p>
                    <p class="font-semibold text-gray-900">{{ $vehicle->yearDisplay }}</p>
                </div>
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500">Km</p>
                    <p class="font-semibold text-gray-900">{{ $vehicle->formattedMileage }}</p>
                </div>
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500">Combustível</p>
                    <p class="font-semibold text-gray-900">{{ $vehicle->fuelLabel }}</p>
                </div>
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500">Câmbio</p>
                    <p class="font-semibold text-gray-900">{{ $vehicle->transmissionLabel }}</p>
                </div>
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500">Cor</p>
                    <p class="font-semibold text-gray-900">{{ $vehicle->color }}</p>
                </div>
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500">Placa</p>
                    <p class="font-semibold text-gray-900">{{ $vehicle->plate ? strtoupper($vehicle->plate) : '—' }}</p>
                </div>
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500">Chassi</p>
                    <p class="font-semibold text-gray-900 text-xs">{{ $vehicle->chassis ?? '—' }}</p>
                </div>
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500">Renavam</p>
                    <p class="font-semibold text-gray-900 text-xs">{{ $vehicle->renavam ?? '—' }}</p>
                </div>
            </div>

            @if($vehicle->features)
            <div class="mt-6">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Opcionais</h4>
                <div class="flex flex-wrap gap-2">
                    @foreach((is_array($vehicle->features) ? $vehicle->features : explode(',', $vehicle->features)) as $feature)
                    <span class="badge bg-primary-50 text-primary-700">{{ trim($feature) }}</span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Related Leads --}}
        @if(isset($leads) && $leads->count() > 0)
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Leads Interessados</h3>
            <div class="space-y-2">
                @foreach($leads as $lead)
                <a href="{{ route('leads.show', $lead) }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center text-xs font-medium">
                            {{ $lead->contact->initials ?? '?' }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $lead->contact->name ?? 'Sem contato' }}</p>
                            <p class="text-xs text-gray-500">{{ $lead->stageLabel }}</p>
                        </div>
                    </div>
                    <span class="badge {{ $lead->stageColor }}">{{ $lead->stageLabel }}</span>
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">
        {{-- Price Card --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <span class="badge {{ $vehicle->status === 'available' ? 'bg-green-100 text-green-700' : ($vehicle->status === 'reserved' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-700') }} text-sm">
                    {{ $vehicle->status === 'available' ? 'Disponível' : ($vehicle->status === 'reserved' ? 'Reservado' : 'Vendido') }}
                </span>
            </div>

            <p class="text-3xl font-bold text-primary-700">{{ $vehicle->formattedPrice }}</p>

            @if($vehicle->fipe_price)
            <div class="mt-2 flex items-center gap-2">
                <p class="text-sm text-gray-500">FIPE: R$ {{ number_format($vehicle->fipe_price, 0, ',', '.') }}</p>
                @php
                    $diff = (($vehicle->price - $vehicle->fipe_price) / $vehicle->fipe_price) * 100;
                @endphp
                <span class="text-xs font-medium {{ $diff > 0 ? 'text-red-500' : 'text-green-500' }}">
                    {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 1) }}%
                </span>
            </div>
            @endif

            @if($vehicle->purchase_price)
            <div class="mt-4 pt-4 border-t">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Custo</span>
                    <span class="font-medium">R$ {{ number_format($vehicle->purchase_price, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm mt-1">
                    <span class="text-gray-500">Margem</span>
                    <span class="font-medium text-green-600">R$ {{ number_format($vehicle->price - $vehicle->purchase_price, 0, ',', '.') }}</span>
                </div>
            </div>
            @endif
        </div>

        {{-- Info --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Informações</h3>
            <dl class="space-y-3">
                <div class="flex justify-between text-sm">
                    <dt class="text-gray-500">Cadastrado em</dt>
                    <dd class="font-medium text-gray-900">{{ $vehicle->created_at->format('d/m/Y') }}</dd>
                </div>
                @if($vehicle->sold_at)
                <div class="flex justify-between text-sm">
                    <dt class="text-gray-500">Vendido em</dt>
                    <dd class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($vehicle->sold_at)->format('d/m/Y') }}</dd>
                </div>
                @endif
                <div class="flex justify-between text-sm">
                    <dt class="text-gray-500">Dias em estoque</dt>
                    <dd class="font-medium text-gray-900">{{ $vehicle->created_at->diffInDays(now()) }} dias</dd>
                </div>
            </dl>
        </div>

        {{-- Notes --}}
        @if($vehicle->notes)
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Observações Internas</h3>
            <p class="text-sm text-gray-600">{{ $vehicle->notes }}</p>
        </div>
        @endif
    </div>
</div>
@endsection