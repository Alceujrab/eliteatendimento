<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TicketsByPriorityChart extends ChartWidget
{
    protected ?string $heading = 'Tickets por Prioridade';
    protected static ?int $sort = 5;
    protected ?string $maxHeight = '300px';
    protected string | null $pollingInterval = '60s';

    protected function getData(): array
    {
        $tenant = filament()->getTenant();

        $data = Ticket::where('tenant_id', $tenant->id)
            ->whereIn('status', ['open', 'in_progress'])
            ->select('priority', DB::raw('count(*) as total'))
            ->groupBy('priority')
            ->pluck('total', 'priority')
            ->toArray();

        $priorities = [
            'low'      => ['label' => 'Baixa', 'color' => '#22c55e'],
            'medium'   => ['label' => 'Média', 'color' => '#f59e0b'],
            'high'     => ['label' => 'Alta', 'color' => '#f97316'],
            'critical' => ['label' => 'Crítica', 'color' => '#ef4444'],
        ];

        $labels = [];
        $values = [];
        $colors = [];

        foreach ($priorities as $key => $info) {
            $labels[] = $info['label'];
            $values[] = $data[$key] ?? 0;
            $colors[] = $info['color'];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Tickets',
                    'data' => $values,
                    'backgroundColor' => $colors,
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
