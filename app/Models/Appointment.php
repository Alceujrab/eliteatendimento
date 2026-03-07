<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Appointment extends Model
{
    protected $fillable = [
        'tenant_id', 'contact_id', 'lead_id', 'user_id', 'vehicle_id', 'type',
        'scheduled_at', 'duration_minutes', 'status', 'notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function contact(): BelongsTo { return $this->belongsTo(Contact::class); }
    public function lead(): BelongsTo { return $this->belongsTo(Lead::class); }
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

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => 'Agendado',
            'confirmed' => 'Confirmado',
            'completed' => 'Realizado',
            'cancelled' => 'Cancelado',
            'no_show' => 'Não Compareceu',
            default => $this->status,
        };
    }

    public static function hasConflict(
        int $tenantId,
        ?int $userId,
        int $contactId,
        Carbon|string $scheduledAt,
        int $durationMinutes = 60,
        ?int $ignoreAppointmentId = null,
    ): bool {
        $start = $scheduledAt instanceof Carbon ? $scheduledAt->copy() : Carbon::parse($scheduledAt);
        $end = $start->copy()->addMinutes(max(1, $durationMinutes));

        $query = static::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->when($ignoreAppointmentId, fn ($builder) => $builder->whereKeyNot($ignoreAppointmentId));

        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('contact_id', $contactId);
        }

        $appointments = $query
            ->whereBetween('scheduled_at', [
                $start->copy()->subDay(),
                $end->copy()->addDay(),
            ])
            ->get(['id', 'scheduled_at', 'duration_minutes']);

        foreach ($appointments as $appointment) {
            $existingStart = Carbon::parse($appointment->scheduled_at);
            $existingEnd = $existingStart->copy()->addMinutes(max(1, (int) ($appointment->duration_minutes ?? 60)));

            if ($existingStart->lt($end) && $existingEnd->gt($start)) {
                return true;
            }
        }

        return false;
    }
}
