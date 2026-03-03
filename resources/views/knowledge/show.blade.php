@extends('layouts.app')

@section('title', $article->title)
@section('page-title', 'Base de Conhecimento')

@section('header-actions')
<div class="flex items-center gap-2">
    <a href="{{ route('knowledge.edit', $article) }}" class="bg-gray-100 text-gray-700 px-3 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors flex items-center gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        Editar
    </a>
    <form method="POST" action="{{ route('knowledge.destroy', $article) }}" onsubmit="return confirm('Excluir artigo?')">
        @csrf @method('DELETE')
        <button class="bg-red-50 text-red-600 px-3 py-2 rounded-lg text-sm font-medium hover:bg-red-100 transition-colors flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            Excluir
        </button>
    </form>
</div>
@endsection

@section('content')
<div class="flex gap-6">
    {{-- Article Content --}}
    <div class="flex-1 min-w-0">
        <div class="bg-white rounded-xl border border-gray-200 p-8">
            {{-- Breadcrumb --}}
            <div class="flex items-center gap-2 text-sm text-gray-400 mb-4">
                <a href="{{ route('knowledge.index') }}" class="hover:text-primary-600">Base de Conhecimento</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                @if($article->category)
                <a href="{{ route('knowledge.index', ['category' => $article->category]) }}" class="hover:text-primary-600">{{ $article->category }}</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                @endif
                <span class="text-gray-600">{{ Str::limit($article->title, 40) }}</span>
            </div>

            {{-- Status Badge --}}
            <div class="flex items-center gap-2 mb-3">
                @if($article->category)
                <span class="badge bg-primary-50 text-primary-600">{{ $article->category }}</span>
                @endif
                @if($article->is_published)
                <span class="badge bg-green-100 text-green-700">Publicado</span>
                @else
                <span class="badge bg-yellow-100 text-yellow-700">Rascunho</span>
                @endif
            </div>

            {{-- Title --}}
            <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ $article->title }}</h1>

            {{-- Content --}}
            <div class="prose prose-sm max-w-none text-gray-700">
                {!! $article->content !!}
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="w-72 shrink-0 space-y-4">
        {{-- Meta --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Informações</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Autor</dt>
                    <dd class="font-medium text-gray-900">{{ $article->author->name ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Slug</dt>
                    <dd class="font-medium text-gray-900 text-xs font-mono">{{ $article->slug }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Criado em</dt>
                    <dd class="font-medium text-gray-900">{{ $article->created_at->format('d/m/Y H:i') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Atualizado em</dt>
                    <dd class="font-medium text-gray-900">{{ $article->updated_at->format('d/m/Y H:i') }}</dd>
                </div>
            </dl>
        </div>

        {{-- Related Articles --}}
        @if(isset($related) && $related->count())
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Artigos Relacionados</h3>
            <div class="space-y-2">
                @foreach($related as $rel)
                <a href="{{ route('knowledge.show', $rel) }}" class="block text-sm text-gray-600 hover:text-primary-600 truncate">
                    {{ $rel->title }}
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection