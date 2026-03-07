<?php

namespace App\Filament\Resources\AppointmentResource\Widgets;

use App\Services\AppointmentsSellerMetricsService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TopSellersRanking extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected ?string $heading = 'Ranking de Vendedores (30 dias)';

    protected function getStats(): array
    {
        $tenant = filament()->getTenant();

        if (! $tenant) {
            return [];
        }

        $rows = app(AppointmentsSellerMetricsService::class)
            ->build($tenant->id, 30, null)
            ->sortByDesc(fn (array $row) => ($row['conversion_rate'] * 0.6) + ($row['attendance_rate'] * 0.4))
            ->take(3)
            ->values();

        if ($rows->isEmpty()) {
            return [
                Stat::make('Ranking', 'Sem dados')
                    ->description('Ainda não há dados suficientes para o período.')
                    ->color('gray'),
            ];
        }

        $positions = [
            1 => '🥇 1º lugar',
            2 => '🥈 2º lugar',
            3 => '🥉 3º lugar',
        ];

        return $rows->map(function (array $row, int $index) use ($positions): Stat {
            $position = $positions[$index + 1] ?? (($index + 1) . 'º lugar');

            $description = sprintf(
                '%s • Comparecimento: %s%% • Conversão: %s%%',
                $position,
                number_format((float) $row['attendance_rate'], 1, ',', '.'),
                number_format((float) $row['conversion_rate'], 1, ',', '.')
            );

            return Stat::make($row['user'], (string) $row['volume'] . ' agenda(s)')
                ->description($description)
                ->color(match ($index) {
                    0 => 'success',
                    1 => 'info',
                    default => 'warning',
                });
        })->all();
    }
}
