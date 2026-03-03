<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'conversation_id', 'contact_id', 'user_id', 'type', 'body',
        'attachments', 'direction', 'status', 'external_id', 'metadata', 'is_internal_note',
    ];

    protected $casts = [
        'attachments' => 'array',
        'metadata' => 'array',
        'is_internal_note' => 'boolean',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isInbound(): bool
    {
        return $this->direction === 'inbound';
    }

    public function isOutbound(): bool
    {
        return $this->direction === 'outbound';
    }

    public function getSenderNameAttribute(): string
    {
        if ($this->is_internal_note) return $this->user?->name ?? 'Sistema';
        if ($this->isInbound()) return $this->contact?->name ?? 'Cliente';
        return $this->user?->name ?? 'Atendente';
    }

    public function getFormattedTimeAttribute(): string
    {
        return $this->created_at->format('H:i');
    }
}
