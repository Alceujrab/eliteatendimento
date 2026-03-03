<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KnowledgeArticleController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $query = KnowledgeArticle::where('tenant_id', $tenantId);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('body', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $articles = $query->latest()->paginate(15);

        $categories = KnowledgeArticle::where('tenant_id', $tenantId)
            ->distinct()
            ->pluck('category')
            ->filter()
            ->sort();

        return view('knowledge.index', compact('articles', 'categories'));
    }

    public function create()
    {
        return view('knowledge.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'category' => 'nullable|string|max:100',
            'is_published' => 'boolean',
            'is_internal' => 'boolean',
        ]);

        $data['tenant_id'] = auth()->user()->tenant_id;
        $data['slug'] = Str::slug($data['title']);
        $data['author_id'] = auth()->id();

        KnowledgeArticle::create($data);

        return redirect()->route('knowledge.index')->with('success', 'Artigo criado.');
    }

    public function show(KnowledgeArticle $article)
    {
        return view('knowledge.show', compact('article'));
    }

    public function edit(KnowledgeArticle $article)
    {
        return view('knowledge.edit', compact('article'));
    }

    public function update(Request $request, KnowledgeArticle $article)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'category' => 'nullable|string|max:100',
            'is_published' => 'boolean',
            'is_internal' => 'boolean',
        ]);

        $data['slug'] = Str::slug($data['title']);

        $article->update($data);

        return redirect()->route('knowledge.show', $article)->with('success', 'Artigo atualizado.');
    }

    public function destroy(KnowledgeArticle $article)
    {
        $article->delete();

        return redirect()->route('knowledge.index')->with('success', 'Artigo excluído.');
    }
}
