<?php

namespace App\Filament\Widgets;

use App\Models\SatisfactionSurvey;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SatisfactionOverview extends BaseWidget
{
    protected static ?int $sort = 4;
    protected string | null $pollingInterval = '60s';
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $tenant = filament()->getTenant();

        $surveys = SatisfactionSurvey::where('tenant_id', $tenant->id);
        $totalSurveys = (clone $surveys)->count();

        if ($totalSurveys === 0) {
            return [
                Stat::make('NPS', 'N/A')
                    ->description('Sem avaliações')
                    ->color('gray'),
                Stat::make('CSAT', 'N/A')
                    ->description('Sem avaliações')
                    ->color('gray'),
                Stat::make('Total de Avaliações', 0)
                    ->color('gray'),
            ];
        }

        // NPS Calculation (based on nps_score 0-10)
        $npsScores = (clone $surveys)->whereNotNull('nps_score');
        $npsTotal = (clone $npsScores)->count();

        $nps = 'N/A';
        $npsColor = 'gray';
        $npsDescription = 'Sem dados de NPS';

        if ($npsTotal > 0) {
            $promoters = (clone $npsScores)->where('nps_score', '>=', 9)->count();
            $detractors = (clone $npsScores)->where('nps_score', '<=', 6)->count();
            $npsValue = round((($promoters - $detractors) / $npsTotal) * 100);
            $nps = $npsValue;

            if ($npsValue >= 70) {
                $npsColor = 'success';
                $npsDescription = 'Excelente! Zona de Excelência';
            } elseif ($npsValue >= 50) {
                $npsColor = 'success';
                $npsDescription = 'Muito bom! Zona de Qualidade';
            } elseif ($npsValue >= 0) {
                $npsColor = 'warning';
                $npsDescription = 'Razoável. Zona de Aperfeiçoamento';
            } else {
                $npsColor = 'danger';
                $npsDescription = 'Atenção! Zona Crítica';
            }
        }

        // CSAT (based on csat_score 1-5)
        $csatScores = (clone $surveys)->whereNotNull('csat_score');
        $csatTotal = (clone $csatScores)->count();

        $csat = 'N/A';
        $csatColor = 'gray';
        $csatDescription = 'Sem dados de CSAT';

        if ($csatTotal > 0) {
            $satisfied = (clone $csatScores)->where('csat_score', '>=', 4)->count();
            $csatValue = round(($satisfied / $csatTotal) * 100);
            $csat = $csatValue . '%';

            if ($csatValue >= 80) {
                $csatColor = 'success';
                $csatDescription = 'Excelente satisfação';
            } elseif ($csatValue >= 60) {
                $csatColor = 'warning';
                $csatDescription = 'Satisfação razoável';
            } else {
                $csatColor = 'danger';
                $csatDescription = 'Satisfação precisa melhorar';
            }
        }

        // Average score
        $avgCsat = $csatTotal > 0
            ? number_format((clone $surveys)->whereNotNull('csat_score')->avg('csat_score'), 1, ',', '.')
            : 'N/A';

        return [
            Stat::make('NPS', $nps)
                ->description($npsDescription)
                ->descriptionIcon('heroicon-m-face-smile')
                ->color($npsColor),

            Stat::make('CSAT', $csat)
                ->description($csatDescription . ' (média: ' . $avgCsat . '/5)')
                ->descriptionIcon('heroicon-m-star')
                ->color($csatColor),

            Stat::make('Total de Avaliações', $totalSurveys)
                ->description($npsTotal . ' NPS · ' . $csatTotal . ' CSAT')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary'),
        ];
    }
}
