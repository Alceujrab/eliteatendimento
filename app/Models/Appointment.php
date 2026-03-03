<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    protected $fillable = [
        'tenant_id', 'contact_id', 'user_id', 'vehicle_id', 'type',
        'scheduled_at', 'status', 'notes',
    ];

    protected $casts = ['scheduled_at' => 'datetime'];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function contact(): BelongsTo { return $this->belongsTo(Contact::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function vehicle(): BelongsTo { return $this->belongsTo(Vehicle::class); }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'test_drive' => 'Test Drive',
            'visit' => 'Visita',
            'delivery' => 'Entrega',
            'maintenance' => 'Manutenção',
            default => $this->type,
        };
    }
}
