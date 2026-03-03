<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id', 'contact_id', 'assigned_to', 'conversation_id', 'number',
        'subject', 'description', 'category', 'priority', 'status',
        'due_at', 'first_response_at', 'resolved_at', 'closed_at', 'tags',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'first_response_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'tags' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function ($ticket) {
            if (!$ticket->number) {
                $last = static::where('tenant_id', $ticket->tenant_id)->max('id') ?? 0;
                $ticket->number = 'TK-' . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class);
    }

    /* ── Helpers ── */

    public function isOverdue(): bool
    {
        return $this->due_at && now()->isAfter($this->due_at) && !in_array($this->status, ['resolved', 'closed']);
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'open' => ['label' => 'Aberto', 'color' => 'blue'],
            'in_progress' => ['label' => 'Em Progresso', 'color' => 'yellow'],
            'waiting' => ['label' => 'Aguardando', 'color' => 'orange'],
            'resolved' => ['label' => 'Resolvido', 'color' => 'green'],
            'closed' => ['label' => 'Fechado', 'color' => 'gray'],
            default => ['label' => $this->status, 'color' => 'gray'],
        };
    }

    public function getPriorityBadgeAttribute(): array
    {
        return match ($this->priority) {
            'low' => ['label' => 'Baixa', 'color' => 'gray'],
            'medium' => ['label' => 'Média', 'color' => 'blue'],
            'high' => ['label' => 'Alta', 'color' => 'orange'],
            'urgent' => ['label' => 'Urgente', 'color' => 'red'],
            default => ['label' => $this->priority, 'color' => 'gray'],
        };
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'duvida' => 'Dúvida',
            'reclamacao' => 'Reclamação',
            'solicitacao' => 'Solicitação',
            'pos_venda' => 'Pós-Venda',
            'financeiro' => 'Financeiro',
            default => $this->category,
        };
    }
}
