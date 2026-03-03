<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadActivity extends Model
{
    protected $fillable = [
        'lead_id', 'user_id', 'type', 'description', 'metadata', 'scheduled_at', 'is_completed',
    ];

    protected $casts = [
        'metadata' => 'array',
        'scheduled_at' => 'datetime',
        'is_completed' => 'boolean',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'note' => 'document-text',
            'call' => 'phone',
            'email' => 'envelope',
            'whatsapp' => 'chat-bubble-left-right',
            'meeting' => 'calendar',
            'follow_up' => 'clock',
            'stage_change' => 'arrow-right',
            default => 'bell',
        };
    }
}
