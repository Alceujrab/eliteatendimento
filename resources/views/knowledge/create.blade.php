@extends('layouts.app')

@section('title', 'Novo Artigo')
@section('page-title', 'Base de Conhecimento')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-6">Novo Artigo</h2>

        <form method="POST" action="{{ route('knowledge.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Título *</label>
                <input type="text" name="title" value="{{ old('title') }}" required
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                       x-data x-on:input="$refs.slug.value = $event.target.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '')">
                @error('title')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                <input type="text" name="slug" value="{{ old('slug') }}" x-ref="slug"
                       class="w-full rounded-lg border-gray-300 text-sm bg-gray-50 focus:border-primary-500 focus:ring-primary-500"
                       placeholder="gerado-automaticamente">
                <p class="mt-1 text-xs text-gray-400">Deixe vazio para gerar automaticamente</p>
                @error('slug')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                <input type="text" name="category" value="{{ old('category') }}" list="categories-list"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                       placeholder="Ex: Financiamento, Documentação, Pós-venda...">
                <datalist id="categories-list">
                    @foreach($categories as $cat)
                    <option value="{{ $cat }}">
                    @endforeach
                </datalist>
                @error('category')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Conteúdo *</label>
                <textarea name="content" rows="16" required
                          class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500 font-mono"
                          placeholder="Escreva o conteúdo do artigo... (suporta HTML)">{{ old('content') }}</textarea>
                <p class="mt-1 text-xs text-gray-400">Dica: você pode usar HTML para formatação (negrito, listas, links, etc.)</p>
                @error('content')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="is_published" class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="0" {{ old('is_published') == '0' ? 'selected' : '' }}>Rascunho</option>
                    <option value="1" {{ old('is_published') == '1' ? 'selected' : '' }}>Publicado</option>
                </select>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
                <a href="{{ route('knowledge.index') }}" class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Cancelar</a>
                <button type="submit" class="px-4 py-2 text-sm text-white bg-primary-600 rounded-lg hover:bg-primary-700">Salvar Artigo</button>
            </div>
        </form>
    </div>
</div>
@endsection