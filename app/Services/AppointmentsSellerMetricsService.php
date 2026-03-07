<?php

namespace App\Services;

use App\Models\Appointment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AppointmentsSellerMetricsService
{
    public function build(int $tenantId, int $days = 30, ?int $limit = 8): Collection
    {
        $startDate = Carbon::now()->subDays(max(1, $days))->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $appointments = Appointment::query()
            ->with(['user:id,name', 'lead:id,stage'])
            ->where('tenant_id', $tenantId)
            ->whereBetween('scheduled_at', [$startDate, $endDate])
            ->whereNotNull('user_id')
            ->whereIn('status', ['completed', 'no_show', 'cancelled', 'scheduled', 'confirmed'])
            ->get(['id', 'user_id', 'lead_id', 'status']);

        $rows = $appointments->groupBy('user_id')
            ->map(function (Collection $items, $userId) {
                $userName = optional($items->first()->user)->name ?? "Usuário {$userId}";

                $attendanceBase = $items->whereIn('status', ['completed', 'no_show'])->count();
                $completed = $items->where('status', 'completed')->count();

                $attendanceRate = $attendanceBase > 0
                    ? round(($completed / $attendanceBase) * 100, 1)
                    : 0.0;

                $completedWithLead = $items
                    ->where('status', 'completed')
                    ->filter(fn (Appointment $appointment) => filled($appointment->lead_id))
                    ->pluck('lead_id')
                    ->unique();

                $wonLeadCount = $items
                    ->where('status', 'completed')
                    ->filter(fn (Appointment $appointment) => filled($appointment->lead_id) && optional($appointment->lead)->stage === 'won')
                    ->pluck('lead_id')
                    ->unique()
                    ->count();

                $conversionBase = $completedWithLead->count();

                $conversionRate = $conversionBase > 0
                    ? round(($wonLeadCount / $conversionBase) * 100, 1)
                    : 0.0;

                return [
                    'user' => $userName,
                    'attendance_rate' => $attendanceRate,
                    'conversion_rate' => $conversionRate,
                    'volume' => $items->count(),
                    'attendance_base' => $attendanceBase,
                    'completed' => $completed,
                    'conversion_base' => $conversionBase,
                    'won_leads' => $wonLeadCount,
                ];
            })
            ->sortByDesc('volume')
            ->values();

        if ($limit !== null) {
            return $rows->take($limit)->values();
        }

        return $rows;
    }
}
