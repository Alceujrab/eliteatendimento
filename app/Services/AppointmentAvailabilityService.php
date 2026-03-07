<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Carbon;

class AppointmentAvailabilityService
{
    public function assertWithinAvailability(
        Tenant $tenant,
        int $userId,
        Carbon|string $scheduledAt,
        int $durationMinutes,
    ): ?string {
        $user = User::query()->where('tenant_id', $tenant->id)->find($userId);

        if (! $user) {
            return 'Responsável inválido para esta empresa.';
        }

        $start = $scheduledAt instanceof Carbon ? $scheduledAt->copy() : Carbon::parse($scheduledAt);
        $end = $start->copy()->addMinutes(max(1, $durationMinutes));

        $dayKey = $this->dayKey($start);
        $workingHours = $this->resolveWorkingHours($tenant, $user, $dayKey);

        if (! $workingHours || ! ($workingHours['enabled'] ?? false)) {
            return 'O responsável não possui expediente configurado para este dia.';
        }

        $dayStart = Carbon::parse($start->format('Y-m-d') . ' ' . $workingHours['start']);
        $dayEnd = Carbon::parse($start->format('Y-m-d') . ' ' . $workingHours['end']);

        if ($dayEnd->lte($dayStart)) {
            return 'Expediente do responsável está inválido. Ajuste o horário em Usuários.';
        }

        if ($start->lt($dayStart) || $end->gt($dayEnd)) {
            return sprintf(
                'Fora do expediente do responsável (%s às %s).',
                $dayStart->format('H:i'),
                $dayEnd->format('H:i')
            );
        }

        return null;
    }

    private function dayKey(Carbon $date): string
    {
        return match ($date->dayOfWeekIso) {
            1 => 'mon',
            2 => 'tue',
            3 => 'wed',
            4 => 'thu',
            5 => 'fri',
            6 => 'sat',
            default => 'sun',
        };
    }

    private function resolveWorkingHours(Tenant $tenant, User $user, string $dayKey): ?array
    {
        $userHours = is_array($user->working_hours) ? ($user->working_hours[$dayKey] ?? null) : null;

        if ($this->isValidHours($userHours)) {
            return [
                'enabled' => (bool) ($userHours['enabled'] ?? true),
                'start' => (string) ($userHours['start'] ?? $userHours['open']),
                'end' => (string) ($userHours['end'] ?? $userHours['close']),
            ];
        }

        $tenantHours = is_array($tenant->business_hours) ? ($tenant->business_hours[$dayKey] ?? null) : null;

        if (! $this->isValidHours($tenantHours)) {
            return null;
        }

        return [
            'enabled' => (bool) ($tenantHours['enabled'] ?? true),
            'start' => (string) ($tenantHours['start'] ?? $tenantHours['open']),
            'end' => (string) ($tenantHours['end'] ?? $tenantHours['close']),
        ];
    }

    private function isValidHours(?array $hours): bool
    {
        if (! is_array($hours)) {
            return false;
        }

        $start = $hours['start'] ?? $hours['open'] ?? null;
        $end = $hours['end'] ?? $hours['close'] ?? null;

        return filled($start) && filled($end);
    }
}
