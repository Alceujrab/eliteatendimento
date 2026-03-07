<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class AppointmentsOverview extends BaseWidget
{
    protected static ?int $sort = 2;

    protected string | null $pollingInterval = '60s';

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $tenant = filament()->getTenant();

        $todayStart = Carbon::today();
        $todayEnd = Carbon::today()->endOfDay();
        $weekEnd = Carbon::now()->copy()->addDays(7);
        $monthStart = Carbon::now()->copy()->subDays(30);

        $todayTotal = Appointment::query()
            ->where('tenant_id', $tenant->id)
            ->whereBetween('scheduled_at', [$todayStart, $todayEnd])
            ->count();

        $todayConfirmed = Appointment::query()
            ->where('tenant_id', $tenant->id)
            ->whereBetween('scheduled_at', [$todayStart, $todayEnd])
            ->whereIn('status', ['confirmed', 'completed'])
            ->count();

        $nextSevenDays = Appointment::query()
            ->where('tenant_id', $tenant->id)
            ->whereBetween('scheduled_at', [Carbon::now(), $weekEnd])
            ->count();

        $pendingNow = Appointment::query()
            ->where('tenant_id', $tenant->id)
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->whereBetween('scheduled_at', [Carbon::now()->subHours(2), Carbon::now()->addDays(1)])
            ->count();

        $baseForRate = Appointment::query()
            ->where('tenant_id', $tenant->id)
            ->whereBetween('scheduled_at', [$monthStart, Carbon::now()])
            ->whereIn('status', ['completed', 'no_show'])
            ->count();

        $noShowCount = Appointment::query()
            ->where('tenant_id', $tenant->id)
            ->whereBetween('scheduled_at', [$monthStart, Carbon::now()])
            ->where('status', 'no_show')
            ->count();

        $noShowRate = $baseForRate > 0
            ? round(($noShowCount / $baseForRate) * 100, 1)
            : 0.0;

        return [
            Stat::make('Agenda Hoje', $todayTotal)
                ->description("{$todayConfirmed} confirmado(s)/realizado(s)")
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make('Próximos 7 dias', $nextSevenDays)
                ->description('Visão semanal da operação')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),

            Stat::make('Compromissos Pendentes', $pendingNow)
                ->description('Janela de 24h')
                ->descriptionIcon('heroicon-m-bell-alert')
                ->color($pendingNow > 0 ? 'warning' : 'success'),

            Stat::make('Taxa de No-show (30d)', number_format($noShowRate, 1, ',', '.') . '%')
                ->description("{$noShowCount} ausência(s) em {$baseForRate} compromisso(s)")
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($noShowRate >= 20 ? 'danger' : ($noShowRate >= 10 ? 'warning' : 'success')),
        ];
    }
}
