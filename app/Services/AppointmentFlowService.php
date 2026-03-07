<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\LeadActivity;

class AppointmentFlowService
{
    public function registerCreated(Appointment $appointment, ?int $actorUserId = null): void
    {
        if (! $appointment->lead_id) {
            return;
        }

        LeadActivity::create([
            'lead_id' => $appointment->lead_id,
            'user_id' => $actorUserId ?? $appointment->user_id,
            'type' => 'meeting',
            'description' => sprintf(
                'Agendamento criado para %s (%s).',
                $appointment->scheduled_at?->format('d/m/Y H:i') ?? '-',
                $appointment->type_label
            ),
            'scheduled_at' => $appointment->scheduled_at,
        ]);
    }

    public function registerStatusChange(Appointment $appointment, string $oldStatus, string $newStatus, ?int $actorUserId = null): void
    {
        if (! $appointment->lead_id || $oldStatus === $newStatus) {
            return;
        }

        LeadActivity::create([
            'lead_id' => $appointment->lead_id,
            'user_id' => $actorUserId ?? $appointment->user_id,
            'type' => 'meeting',
            'description' => sprintf(
                'Agendamento alterado de %s para %s (%s).',
                $this->statusLabel($oldStatus),
                $this->statusLabel($newStatus),
                $appointment->scheduled_at?->format('d/m/Y H:i') ?? '-'
            ),
            'scheduled_at' => $appointment->scheduled_at,
        ]);
    }

    public function registerReminder(Appointment $appointment): void
    {
        if (! $appointment->lead_id) {
            return;
        }

        LeadActivity::create([
            'lead_id' => $appointment->lead_id,
            'user_id' => $appointment->user_id,
            'type' => 'follow_up',
            'description' => sprintf(
                'Lembrete automático: compromisso %s às %s.',
                $appointment->type_label,
                $appointment->scheduled_at?->format('d/m/Y H:i') ?? '-'
            ),
            'scheduled_at' => $appointment->scheduled_at,
        ]);
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'scheduled' => 'Agendado',
            'confirmed' => 'Confirmado',
            'completed' => 'Realizado',
            'cancelled' => 'Cancelado',
            'no_show' => 'Não Compareceu',
            default => $status,
        };
    }
}
