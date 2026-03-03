<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Channel extends Model
{
    protected $fillable = [
        'tenant_id', 'type', 'name', 'identifier', 'credentials', 'settings', 'is_active',
    ];

    protected $casts = [
        'credentials' => 'encrypted:array',
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function getIconAttribute(): string
    {
        return match ($this->type) {
            'whatsapp_meta', 'whatsapp_evolution' => 'whatsapp',
            'facebook' => 'facebook',
            'instagram' => 'instagram',
            'telegram' => 'telegram',
            'email' => 'email',
            'webchat' => 'chat',
            'sms' => 'sms',
            default => 'chat',
        };
    }

    public function getColorAttribute(): string
    {
        return match ($this->type) {
            'whatsapp_meta', 'whatsapp_evolution' => '#25D366',
            'facebook' => '#1877F2',
            'instagram' => '#E4405F',
            'telegram' => '#0088CC',
            'email' => '#6B7280',
            'webchat' => '#1e40af',
            'sms' => '#F59E0B',
            default => '#6B7280',
        };
    }
}
