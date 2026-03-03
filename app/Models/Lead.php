<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id', 'contact_id', 'assigned_to', 'conversation_id', 'stage',
        'temperature', 'estimated_value', 'vehicle_interest', 'notes', 'source',
        'lost_reason', 'next_follow_up', 'won_at', 'lost_at',
    ];

    protected $casts = [
        'estimated_value' => 'decimal:2',
        'next_follow_up' => 'datetime',
        'won_at' => 'datetime',
        'lost_at' => 'datetime',
    ];

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

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class);
    }

    /* ── Helpers ── */

    public function getStageLabelAttribute(): string
    {
        return match ($this->stage) {
            'new' => 'Novo Lead',
            'qualified' => 'Qualificado',
            'proposal' => 'Proposta',
            'negotiation' => 'Negociação',
            'won' => 'Ganho',
            'lost' => 'Perdido',
            default => $this->stage,
        };
    }

    public function getTemperatureColorAttribute(): string
    {
        return match ($this->temperature) {
            'hot' => 'red',
            'warm' => 'yellow',
            'cold' => 'blue',
            default => 'gray',
        };
    }

    public function getStageColorAttribute(): string
    {
        return match ($this->stage) {
            'new' => 'blue',
            'qualified' => 'indigo',
            'proposal' => 'purple',
            'negotiation' => 'yellow',
            'won' => 'green',
            'lost' => 'red',
            default => 'gray',
        };
    }

    public static function stages(): array
    {
        return ['new', 'qualified', 'proposal', 'negotiation', 'won', 'lost'];
    }

    public static function activeStages(): array
    {
        return ['new', 'qualified', 'proposal', 'negotiation'];
    }
}
