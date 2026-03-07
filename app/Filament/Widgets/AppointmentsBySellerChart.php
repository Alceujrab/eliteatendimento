<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class AppointmentsBySellerChart extends ChartWidget
{
    protected ?string $heading = 'Agenda por Vendedor (30 dias)';

    protected static ?int $sort = 3;

    protected ?string $maxHeight = '320px';

    protected string | null $pollingInterval = '60s';

    protected function getData(): array
    {
        $tenant = filament()->getTenant();

        $startDate = Carbon::now()->subDays(30)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $appointments = Appointment::query()
            ->with(['user:id,name', 'lead:id,stage'])
            ->where('tenant_id', $tenant->id)
            ->whereBetween('scheduled_at', [$startDate, $endDate])
            ->whereNotNull('user_id')
            ->whereIn('status', ['completed', 'no_show', 'cancelled', 'scheduled', 'confirmed'])
            ->get(['id', 'user_id', 'lead_id', 'status']);

        $bySeller = $appointments->groupBy('user_id');

        $rows = $bySeller->map(function ($items, $userId) {
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
            ];
        })
            ->sortByDesc('volume')
            ->take(8)
            ->values();

        return [
            'datasets' => [
                [
                    'label' => 'Comparecimento (%)',
                    'data' => $rows->pluck('attendance_rate')->all(),
                    'backgroundColor' => '#3b82f6',
                    'borderRadius' => 4,
                ],
                [
                    'label' => 'Conversão (%)',
                    'data' => $rows->pluck('conversion_rate')->all(),
                    'backgroundColor' => '#22c55e',
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $rows->pluck('user')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'max' => 100,
                    'ticks' => [
                        'callback' => 'function(value) { return value + "%"; }',
                    ],
                ],
            ],
        ];
    }
}
