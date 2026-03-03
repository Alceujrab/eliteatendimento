<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class LeadsFunnelChart extends ChartWidget
{
    protected ?string $heading = 'Funil de Vendas';
    protected static ?int $sort = 3;
    protected ?string $maxHeight = '300px';
    protected string | null $pollingInterval = '60s';

    protected function getData(): array
    {
        $tenant = filament()->getTenant();

        $stages = [
            'new'         => 'Novo',
            'contacted'   => 'Contatado',
            'qualified'   => 'Qualificado',
            'proposal'    => 'Proposta',
            'negotiation' => 'Negociação',
            'won'         => 'Ganho',
            'lost'        => 'Perdido',
        ];

        $data = Lead::where('tenant_id', $tenant->id)
            ->select('stage', DB::raw('count(*) as total'))
            ->groupBy('stage')
            ->pluck('total', 'stage')
            ->toArray();

        $values = [];
        $labels = [];
        $colors = [];

        $stageColors = [
            'new'         => '#94a3b8',
            'contacted'   => '#60a5fa',
            'qualified'   => '#a78bfa',
            'proposal'    => '#f59e0b',
            'negotiation' => '#fb923c',
            'won'         => '#22c55e',
            'lost'        => '#ef4444',
        ];

        foreach ($stages as $key => $label) {
            $labels[] = $label;
            $values[] = $data[$key] ?? 0;
            $colors[] = $stageColors[$key] ?? '#94a3b8';
        }

        return [
            'datasets' => [
                [
                    'label' => 'Leads',
                    'data' => $values,
                    'backgroundColor' => $colors,
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $labels,
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
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
        ];
    }
}
