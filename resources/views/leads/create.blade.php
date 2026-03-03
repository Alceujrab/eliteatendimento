@extends('layouts.app')

@section('title', 'Novo Lead')
@section('page-title', 'Novo Lead')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <form method="POST" action="{{ route('leads.store') }}" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contato</label>
                    <select name="contact_id" required class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="">Selecione...</option>
                        @foreach(\App\Models\Contact::where('tenant_id', auth()->user()->tenant_id)->orderBy('name')->get() as $contact)
                            <option value="{{ $contact->id }}">{{ $contact->name }} {{ $contact->phone ? '- '.$contact->phone : '' }}</option>
                        @endforeach
                    </select>
                    @error('contact_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Responsável</label>
                    <select name="assigned_to" class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="">Sem atribuição</option>
                        @foreach(\App\Models\User::where('tenant_id', auth()->user()->tenant_id)->where('is_active', true)->get() as $user)
                            <option value="{{ $user->id }}" {{ auth()->id() == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Veículo de Interesse</label>
                <input type="text" name="vehicle_interest" value="{{ old('vehicle_interest') }}" placeholder="Ex: Honda Civic 2023"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Temperatura</label>
                    <select name="temperature" class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="warm">Morno</option>
                        <option value="hot">Quente</option>
                        <option value="cold">Frio</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Valor Estimado</label>
                    <input type="number" name="estimated_value" value="{{ old('estimated_value') }}" placeholder="0,00" step="0.01"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Origem</label>
                    <select name="source" class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="whatsapp">WhatsApp</option>
                        <option value="website">Website</option>
                        <option value="instagram">Instagram</option>
                        <option value="facebook">Facebook</option>
                        <option value="indicacao">Indicação</option>
                        <option value="loja">Loja Física</option>
                        <option value="telefone">Telefone</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                <textarea name="notes" rows="3" class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500" placeholder="Observações sobre o lead...">{{ old('notes') }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t">
                <a href="{{ route('leads.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">Cancelar</a>
                <button type="submit" class="bg-primary-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors">
                    Criar Lead
                </button>
            </div>
        </form>
    </div>
</div>
@endsection