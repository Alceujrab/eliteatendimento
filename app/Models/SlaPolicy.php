<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlaPolicy extends Model
{
    protected $fillable = [
        'tenant_id', 'name', 'category', 'priority', 'first_response_minutes',
        'resolution_minutes', 'is_default', 'is_active',
    ];

    protected $casts = ['is_default' => 'boolean', 'is_active' => 'boolean'];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
}
