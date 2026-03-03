<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id', 'contact_id', 'channel_id', 'assigned_to', 'status',
        'priority', 'channel_conversation_id', 'last_message_preview',
        'last_message_at', 'first_response_at', 'resolved_at', 'unread_count', 'metadata',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'first_response_at' => 'datetime',
        'resolved_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    /* ── Scopes ── */

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['new', 'open', 'pending']);
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /* ── Helpers ── */

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'new' => ['label' => 'Novo', 'color' => 'blue'],
            'open' => ['label' => 'Aberto', 'color' => 'green'],
            'pending' => ['label' => 'Aguardando', 'color' => 'yellow'],
            'resolved' => ['label' => 'Resolvido', 'color' => 'gray'],
            'archived' => ['label' => 'Arquivado', 'color' => 'gray'],
            default => ['label' => $this->status, 'color' => 'gray'],
        };
    }

    public function getWaitingTimeAttribute(): string
    {
        if (!$this->last_message_at) return '-';
        $diff = now()->diff($this->last_message_at);
        if ($diff->d > 0) return $diff->d . 'd';
        if ($diff->h > 0) return $diff->h . 'h';
        return $diff->i . 'min';
    }
}
