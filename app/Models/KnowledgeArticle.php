<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class KnowledgeArticle extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id', 'author_id', 'title', 'slug', 'body', 'category',
        'tags', 'is_published', 'is_internal', 'views_count', 'helpful_count',
    ];

    protected $casts = ['tags' => 'array', 'is_published' => 'boolean', 'is_internal' => 'boolean'];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function author(): BelongsTo { return $this->belongsTo(User::class, 'author_id'); }
}
