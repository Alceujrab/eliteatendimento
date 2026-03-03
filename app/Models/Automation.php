<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Automation extends Model
{
    protected $fillable = [
        'tenant_id', 'name', 'description', 'trigger_type', 'trigger_conditions',
        'n8n_workflow_id', 'n8n_webhook_url', 'is_active', 'executions_count',
    ];

    protected $casts = ['trigger_conditions' => 'array', 'is_active' => 'boolean'];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
}
