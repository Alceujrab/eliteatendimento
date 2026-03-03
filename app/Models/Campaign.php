<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id', 'created_by', 'name', 'description', 'type', 'status',
        'message_template', 'media', 'audience_filter', 'total_recipients',
        'sent_count', 'delivered_count', 'read_count', 'replied_count', 'failed_count',
        'scheduled_at', 'started_at', 'completed_at',
    ];

    protected $casts = [
        'media' => 'array',
        'audience_filter' => 'array',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(CampaignMessage::class);
    }

    public function getDeliveryRateAttribute(): float
    {
        return $this->sent_count > 0 ? round(($this->delivered_count / $this->sent_count) * 100, 1) : 0;
    }

    public function getReadRateAttribute(): float
    {
        return $this->delivered_count > 0 ? round(($this->read_count / $this->delivered_count) * 100, 1) : 0;
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'draft' => ['label' => 'Rascunho', 'color' => 'gray'],
            'scheduled' => ['label' => 'Agendada', 'color' => 'blue'],
            'running' => ['label' => 'Em Execução', 'color' => 'yellow'],
            'paused' => ['label' => 'Pausada', 'color' => 'orange'],
            'completed' => ['label' => 'Concluída', 'color' => 'green'],
            'cancelled' => ['label' => 'Cancelada', 'color' => 'red'],
            default => ['label' => $this->status, 'color' => 'gray'],
        };
    }
}
