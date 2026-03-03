<?php

namespace App\Filament\Pages;

use App\Models\Conversation;
use App\Models\Lead;
use App\Models\Ticket;
use App\Models\SatisfactionSurvey;
use App\Models\Contact;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Reports extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static ?string $navigationLabel = 'Relatórios';
    protected static ?string $title = 'Relatórios e Métricas';
    protected static string | \UnitEnum | null $navigationGroup = 'Configurações';
    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.reports';

    public ?string $period = '30';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('period')
                    ->label('Período')
                    ->options([
                        '7' => 'Últimos 7 dias',
                        '15' => 'Últimos 15 dias',
                        '30' => 'Últimos 30 dias',
                        '60' => 'Últimos 60 dias',
                        '90' => 'Últimos 90 dias',
                    ])
                    ->default('30')
                    ->live()
                    ->afterStateUpdated(fn () => null),
            ]);
    }

    protected function getStartDate(): Carbon
    {
        return now()->subDays((int) ($this->period ?? 30));
    }

    public function getConversationMetrics(): array
    {
        $tenant = filament()->getTenant();
        $start = $this->getStartDate();

        $total = Conversation::where('tenant_id', $tenant->id)
            ->where('created_at', '>=', $start)->count();

        $resolved = Conversation::where('tenant_id', $tenant->id)
            ->where('created_at', '>=', $start)
            ->where('status', 'resolved')->count();

        $avgResponseMinutes = Conversation::where('tenant_id', $tenant->id)
            ->where('created_at', '>=', $start)
            ->whereNotNull('first_response_at')
            ->avg(DB::raw("(julianday(first_response_at) - julianday(created_at)) * 24 * 60"));

        $byStatus = Conversation::where('tenant_id', $tenant->id)
            ->where('created_at', '>=', $start)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')->toArray();

        return [
            'total' => $total,
            'resolved' => $resolved,
            'resolution_rate' => $total > 0 ? round(($resolved / $total) * 100, 1) : 0,
            'avg_response_minutes' => $avgResponseMinutes ? round($avgResponseMinutes, 1) : null,
            'by_status' => $byStatus,
        ];
    }

    public function getLeadMetrics(): array
    {
        $tenant = filament()->getTenant();
        $start = $this->getStartDate();

        $total = Lead::where('tenant_id', $tenant->id)
            ->where('created_at', '>=', $start)->count();

        $won = Lead::where('tenant_id', $tenant->id)
            ->where('created_at', '>=', $start)
            ->where('stage', 'won')->count();

        $lost = Lead::where('tenant_id', $tenant->id)
            ->where('created_at', '>=', $start)
            ->where('stage', 'lost')->count();

        $totalValue = Lead::where('tenant_id', $tenant->id)
            ->where('created_at', '>=', $start)
            ->where('stage', 'won')->sum('estimated_value');

        $avgCycleTime = Lead::where('tenant_id', $tenant->id)
            ->where('created_at', '>=', $start)
            ->where('stage', 'won')
            ->avg(DB::raw("(julianday(updated_at) - julianday(created_at))"));

        $byStage = Lead::where('tenant_id', $tenant->id)
            ->where('created_at', '>=', $start)
            ->select('stage', DB::raw('count(*) as total'))
            ->groupBy('stage')
            ->pluck('total', 'stage')->toArray();

        return [
            'total' => $total,
            'won' => $won,
            'lost' => $lost,
            'conversion_rate' => $total > 0 ? round(($won / $total) * 100, 1) : 0,
            'total_value' => $totalValue,
            'avg_cycle_days' => $avgCycleTime ? round($avgCycleTime, 1) : null,
            'by_stage' => $byStage,
        ];
    }

    public function getTicketMetrics(): array
    {
        $tenant = filament()->getTenant();
        $start = $this->getStartDate();

        $total = Ticket::where('tenant_id', $tenant->id)
            ->where('created_at', '>=', $start)->count();

        $resolved = Ticket::where('tenant_id', $tenant->id)
            ->where('created_at', '>=', $start)
            ->where('status', 'resolved')->count();

        $avgResolutionTime = Ticket::where('tenant_id', $tenant->id)
            ->where('created_at', '>=', $start)
            ->where('status', 'resolved')
            ->whereNotNull('resolved_at')
            ->avg(DB::raw("(julianday(resolved_at) - julianday(created_at)) * 24"));

        $byCategory = Ticket::where('tenant_id', $tenant->id)
            ->where('created_at', '>=', $start)
            ->select('category', DB::raw('count(*) as total'))
            ->groupBy('category')
            ->pluck('total', 'category')->toArray();

        $byPriority = Ticket::where('tenant_id', $tenant->id)
            ->where('created_at', '>=', $start)
            ->select('priority', DB::raw('count(*) as total'))
            ->groupBy('priority')
            ->pluck('total', 'priority')->toArray();

        $overdue = Ticket::where('tenant_id', $tenant->id)
            ->where('created_at', '>=', $start)
            ->whereIn('status', ['open', 'in_progress'])
            ->where('due_at', '<', now())->count();

        return [
            'total' => $total,
            'resolved' => $resolved,
            'resolution_rate' => $total > 0 ? round(($resolved / $total) * 100, 1) : 0,
            'avg_resolution_hours' => $avgResolutionTime ? round($avgResolutionTime, 1) : null,
            'overdue' => $overdue,
            'by_category' => $byCategory,
            'by_priority' => $byPriority,
        ];
    }

    public function getSatisfactionMetrics(): array
    {
        $tenant = filament()->getTenant();
        $start = $this->getStartDate();

        $surveys = SatisfactionSurvey::where('tenant_id', $tenant->id)
            ->where('created_at', '>=', $start);

        $total = (clone $surveys)->count();

        $npsScores = (clone $surveys)->whereNotNull('nps_score');
        $npsTotal = (clone $npsScores)->count();
        $nps = null;
        if ($npsTotal > 0) {
            $promoters = (clone $npsScores)->where('nps_score', '>=', 9)->count();
            $detractors = (clone $npsScores)->where('nps_score', '<=', 6)->count();
            $nps = round((($promoters - $detractors) / $npsTotal) * 100);
        }

        $csatScores = (clone $surveys)->whereNotNull('csat_score');
        $csatTotal = (clone $csatScores)->count();
        $csat = null;
        if ($csatTotal > 0) {
            $satisfied = (clone $csatScores)->where('csat_score', '>=', 4)->count();
            $csat = round(($satisfied / $csatTotal) * 100);
        }

        $avgCsat = $csatTotal > 0
            ? round((clone $surveys)->whereNotNull('csat_score')->avg('csat_score'), 2)
            : null;

        return [
            'total_surveys' => $total,
            'nps' => $nps,
            'csat_percent' => $csat,
            'avg_csat' => $avgCsat,
        ];
    }

    public function getContactGrowth(): array
    {
        $tenant = filament()->getTenant();
        $start = $this->getStartDate();

        return Contact::where('tenant_id', $tenant->id)
            ->where('created_at', '>=', $start)
            ->select(DB::raw("strftime('%Y-%m-%d', created_at) as date"), DB::raw('count(*) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();
    }
}
