@extends('layouts.app')

@section('title', 'Contatos')
@section('page-title', 'Contatos')

@section('header-actions')
<a href="{{ route('contacts.create') }}" class="bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    Novo Contato
</a>
@endsection

@section('content')
<div class="space-y-4">
    {{-- Search --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" class="flex gap-3">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nome, telefone ou e-mail..."
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
            </div>
            <button type="submit" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Buscar
            </button>
        </form>
    </div>

    {{-- Total --}}
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ $contacts->total() }} contato{{ $contacts->total() !== 1 ? 's' : '' }} encontrado{{ $contacts->total() !== 1 ? 's' : '' }}</p>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contato</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Telefone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">E-mail</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Conversas</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Leads</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cadastrado</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($contacts as $contact)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-medium {{ $contact->avatarUrl ? '' : 'bg-primary-100 text-primary-600' }}">
                                @if($contact->avatarUrl)
                                    <img src="{{ $contact->avatarUrl }}" class="w-9 h-9 rounded-full object-cover">
                                @else
                                    {{ $contact->initials }}
                                @endif
                            </div>
                            <div>
                                <a href="{{ route('contacts.show', $contact) }}" class="text-sm font-medium text-gray-900 hover:text-primary-600">{{ $contact->name }}</a>
                                @if($contact->company)
                                <p class="text-xs text-gray-500">{{ $contact->company }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $contact->phone ?? '—' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $contact->email ?? '—' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $contact->conversations_count ?? 0 }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $contact->leads_count ?? 0 }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $contact->created_at->format('d/m/Y') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <div class="flex items-center gap-2 justify-end">
                            <a href="{{ route('contacts.edit', $contact) }}" class="text-gray-400 hover:text-primary-600" title="Editar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">Nenhum contato encontrado</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $contacts->links() }}
</div>
@endsection