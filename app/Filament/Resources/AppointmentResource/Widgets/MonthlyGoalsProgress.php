<?php

namespace App\Filament\Resources\AppointmentResource\Widgets;

use App\Models\Appointment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class MonthlyGoalsProgress extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected ?string $heading = 'Metas Mensais da Agenda';

    protected function getStats(): array
    {
        $tenant = filament()->getTenant();

        if (! $tenant) {
            return [];
        }

        $settings = is_array($tenant->settings) ? $tenant->settings : [];
        $goals = is_array($settings['agenda_goals'] ?? null) ? $settings['agenda_goals'] : [];

        $goalAppointments = max(1, (int) ($goals['appointments'] ?? 120));
        $goalAttendance = max(1, (int) ($goals['attendance'] ?? 75));
        $goalConversion = max(1, (int) ($goals['conversion'] ?? 20));

        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        $monthAppointments = Appointment::query()
            ->where('tenant_id', $tenant->id)
            ->whereBetween('scheduled_at', [$start, $end])
            ->count();

        $attendanceBase = Appointment::query()
            ->where('tenant_id', $tenant->id)
            ->whereBetween('scheduled_at', [$start, $end])
            ->whereIn('status', ['completed', 'no_show'])
            ->count();

        $completed = Appointment::query()
            ->where('tenant_id', $tenant->id)
            ->whereBetween('scheduled_at', [$start, $end])
            ->where('status', 'completed')
            ->count();

        $attendanceRate = $attendanceBase > 0
            ? round(($completed / $attendanceBase) * 100, 1)
            : 0.0;

        $completedWithLead = Appointment::query()
            ->where('tenant_id', $tenant->id)
            ->whereBetween('scheduled_at', [$start, $end])
            ->where('status', 'completed')
            ->whereNotNull('lead_id')
            ->pluck('lead_id')
            ->unique();

        $wonLeads = Appointment::query()
            ->with('lead:id,stage')
            ->where('tenant_id', $tenant->id)
            ->whereBetween('scheduled_at', [$start, $end])
            ->where('status', 'completed')
            ->whereNotNull('lead_id')
            ->get(['id', 'lead_id'])
            ->filter(fn (Appointment $appointment) => optional($appointment->lead)->stage === 'won')
            ->pluck('lead_id')
            ->unique()
            ->count();

        $conversionBase = $completedWithLead->count();

        $conversionRate = $conversionBase > 0
            ? round(($wonLeads / $conversionBase) * 100, 1)
            : 0.0;

        $appointmentsProgress = round(min(100, ($monthAppointments / $goalAppointments) * 100), 1);

        return [
            Stat::make('Meta de Agendamentos', $monthAppointments . ' / ' . $goalAppointments)
                ->description('Progresso do mês: ' . number_format($appointmentsProgress, 1, ',', '.') . '%')
                ->descriptionIcon('heroicon-m-calendar')
                ->color($appointmentsProgress >= 100 ? 'success' : ($appointmentsProgress >= 70 ? 'warning' : 'gray')),

            Stat::make('Meta de Comparecimento', number_format($attendanceRate, 1, ',', '.') . '%')
                ->description('Meta: ' . $goalAttendance . '%')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color($attendanceRate >= $goalAttendance ? 'success' : 'warning'),

            Stat::make('Meta de Conversão', number_format($conversionRate, 1, ',', '.') . '%')
                ->description('Meta: ' . $goalConversion . '%')
                ->descriptionIcon('heroicon-m-chart-bar-square')
                ->color($conversionRate >= $goalConversion ? 'success' : 'warning'),
        ];
    }
}
