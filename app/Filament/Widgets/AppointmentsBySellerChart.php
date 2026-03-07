<?php

namespace App\Filament\Widgets;

use App\Services\AppointmentsSellerMetricsService;
use Filament\Widgets\ChartWidget;

class AppointmentsBySellerChart extends ChartWidget
{
    protected ?string $heading = 'Agenda por Vendedor';

    protected static ?int $sort = 3;

    protected ?string $maxHeight = '320px';

    protected string | null $pollingInterval = '60s';

    public ?string $filter = '30';

    protected function getFilters(): ?array
    {
        return [
            '7' => 'Últimos 7 dias',
            '30' => 'Últimos 30 dias',
            '90' => 'Últimos 90 dias',
        ];
    }

    protected function getData(): array
    {
        $tenant = filament()->getTenant();

        $days = (int) ($this->filter ?: 30);

        $rows = app(AppointmentsSellerMetricsService::class)
            ->build($tenant->id, $days, 8);

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
