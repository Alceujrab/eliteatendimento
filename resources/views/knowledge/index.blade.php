@extends('layouts.app')

@section('title', 'Base de Conhecimento')
@section('page-title', 'Base de Conhecimento')

@section('header-actions')
<a href="{{ route('knowledge.create') }}" class="bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    Novo Artigo
</a>
@endsection

@section('content')
<div class="space-y-4">
    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" class="flex gap-3">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar artigos..."
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
            </div>
            <div>
                <select name="category" class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">Todas Categorias</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200">Filtrar</button>
        </form>
    </div>

    {{-- Categories Summary --}}
    @if($categories->count())
    <div class="flex flex-wrap gap-2">
        @foreach($categories as $cat)
        <a href="{{ route('knowledge.index', ['category' => $cat]) }}"
           class="badge {{ request('category') === $cat ? 'bg-primary-100 text-primary-700' : 'bg-gray-100 text-gray-600' }} hover:bg-primary-100 hover:text-primary-700 cursor-pointer">
            {{ $cat }}
        </a>
        @endforeach
    </div>
    @endif

    {{-- Articles --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($articles as $article)
        <a href="{{ route('knowledge.show', $article) }}" class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 mb-2">
                @if($article->category)
                <span class="badge bg-primary-50 text-primary-600 text-xs">{{ $article->category }}</span>
                @endif
                @if(!$article->is_published)
                <span class="badge bg-yellow-100 text-yellow-700 text-xs">Rascunho</span>
                @endif
            </div>
            <h3 class="text-base font-semibold text-gray-900 mb-2">{{ $article->title }}</h3>
            <p class="text-sm text-gray-500 line-clamp-3">{{ Str::limit(strip_tags($article->content), 150) }}</p>
            <div class="mt-3 pt-3 border-t flex items-center justify-between text-xs text-gray-400">
                <span>{{ $article->author->name ?? 'Sem autor' }}</span>
                <span>{{ $article->updated_at->format('d/m/Y') }}</span>
            </div>
        </a>
        @empty
        <div class="col-span-full text-center py-12 text-gray-500">
            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            <p>Nenhum artigo encontrado</p>
        </div>
        @endforelse
    </div>

    {{ $articles->links() }}
</div>
@endsection