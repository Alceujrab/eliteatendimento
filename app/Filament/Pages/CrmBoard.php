<?php

namespace App\Filament\Pages;

use App\Filament\Resources\LeadResource;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\User;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

class CrmBoard extends Page
{
    protected string $view = 'filament.pages.crm-board';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $navigationLabel = 'CRM Board';
    protected static ?string $title = 'CRM Board';
    protected static string | \UnitEnum | null $navigationGroup = 'Vendas';
    protected static ?int $navigationSort = 0;

    public string $searchQuery = '';
    public string $filterSector = 'all';
    public string $filterTag = 'all';
    public string $filterAgent = 'all';
    public string $filterChannel = 'all';
    public string $filterPeriod = 'all';
    public string $sortOrder = 'recent';

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    public function getHeading(): string | Htmlable | null
    {
        return '';
    }

    public static function stageMap(): array
    {
        return [
            'new' => ['label' => 'Novo', 'color' => '#64748b'],
            'qualified' => ['label' => 'Qualificado', 'color' => '#2563eb'],
            'proposal' => ['label' => 'Proposta', 'color' => '#f59e0b'],
            'negotiation' => ['label' => 'Negociacao', 'color' => '#8b5cf6'],
            'won' => ['label' => 'Ganho', 'color' => '#16a34a'],
            'lost' => ['label' => 'Perdido', 'color' => '#ef4444'],
        ];
    }

    #[Computed]
    public function agents(): Collection
    {
        $tenant = filament()->getTenant();

        return User::query()
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    #[Computed]
    public function sectors(): Collection
    {
        $tenant = filament()->getTenant();

        return Contact::query()
            ->where('tenant_id', $tenant->id)
            ->whereNotNull('source')
            ->where('source', '!=', '')
            ->select('source')
            ->distinct()
            ->orderBy('source')
            ->pluck('source');
    }

    #[Computed]
    public function tags(): Collection
    {
        $tenant = filament()->getTenant();

        return Contact::query()
            ->where('tenant_id', $tenant->id)
            ->whereNotNull('tags')
            ->pluck('tags')
            ->flatten()
            ->filter(fn ($tag) => filled($tag))
            ->map(fn ($tag) => (string) $tag)
            ->unique()
            ->sort()
            ->values();
    }

    #[Computed]
    public function channels(): Collection
    {
        $tenant = filament()->getTenant();

        return Lead::query()
            ->where('tenant_id', $tenant->id)
            ->whereNotNull('source')
            ->where('source', '!=', '')
            ->select('source')
            ->distinct()
            ->orderBy('source')
            ->pluck('source');
    }

    #[Computed]
    public function filteredLeads(): Collection
    {
        return $this->filteredQuery()
            ->with(['contact', 'assignedUser'])
            ->limit(300)
            ->get();
    }

    #[Computed]
    public function leadsByStage(): array
    {
        $grouped = $this->filteredLeads->groupBy('stage');
        $columns = [];

        foreach (array_keys(self::stageMap()) as $stage) {
            $columns[$stage] = $grouped->get($stage, collect());
        }

        return $columns;
    }

    #[Computed]
    public function boardStats(): array
    {
        $leads = $this->filteredLeads;

        return [
            'total' => $leads->count(),
            'open' => $leads->whereIn('stage', Lead::activeStages())->count(),
            'won' => $leads->where('stage', 'won')->count(),
            'lost' => $leads->where('stage', 'lost')->count(),
            'open_value' => $leads
                ->whereIn('stage', Lead::activeStages())
                ->sum(fn (Lead $lead) => (float) ($lead->estimated_value ?? 0)),
        ];
    }

    public function sourceLabel(?string $source): string
    {
        return match ($source) {
            'whatsapp' => 'WhatsApp',
            'facebook' => 'Facebook',
            'instagram' => 'Instagram',
            'website' => 'Website',
            'referral' => 'Indicacao',
            'walk_in' => 'Presencial',
            'phone' => 'Telefone',
            default => ucfirst((string) $source),
        };
    }

    public function temperatureLabel(?string $temperature): string
    {
        return match ($temperature) {
            'hot' => 'Quente',
            'warm' => 'Morno',
            'cold' => 'Frio',
            default => 'Sem temperatura',
        };
    }

    public function temperatureColor(?string $temperature): string
    {
        return match ($temperature) {
            'hot' => '#ef4444',
            'warm' => '#f59e0b',
            'cold' => '#3b82f6',
            default => '#94a3b8',
        };
    }

    public function getCreateLeadUrl(): string
    {
        return LeadResource::getUrl('create');
    }

    public function getEditLeadUrl(Lead $lead): string
    {
        return LeadResource::getUrl('edit', ['record' => $lead]);
    }

    public function moveLead(int $leadId, string $stage): void
    {
        if (!array_key_exists($stage, self::stageMap())) {
            return;
        }

        $tenant = filament()->getTenant();
        $lead = Lead::query()
            ->where('tenant_id', $tenant->id)
            ->find($leadId);

        if (!$lead || $lead->stage === $stage) {
            return;
        }

        $fromStage = $lead->stage;
        $updates = ['stage' => $stage];

        if ($stage === 'won') {
            $updates['won_at'] = now();
            $updates['lost_at'] = null;
        } elseif ($stage === 'lost') {
            $updates['lost_at'] = now();
            $updates['won_at'] = null;
        } else {
            $updates['won_at'] = null;
            $updates['lost_at'] = null;
        }

        $lead->update($updates);

        $lead->activities()->create([
            'user_id' => Auth::id(),
            'type' => 'stage_change',
            'description' => 'Movido de ' . self::stageMap()[$fromStage]['label'] . ' para ' . self::stageMap()[$stage]['label'],
            'metadata' => ['from' => $fromStage, 'to' => $stage],
        ]);
    }

    public function toggleSortOrder(): void
    {
        $this->sortOrder = $this->sortOrder === 'recent' ? 'oldest' : 'recent';
    }

    public function clearFilters(): void
    {
        $this->filterSector = 'all';
        $this->filterTag = 'all';
        $this->filterAgent = 'all';
        $this->filterChannel = 'all';
        $this->filterPeriod = 'all';
    }

    protected function filteredQuery(): Builder
    {
        $tenant = filament()->getTenant();
        $query = Lead::query()->where('tenant_id', $tenant->id);

        if (filled($this->searchQuery)) {
            $search = $this->searchQuery;

            $query->where(function (Builder $q) use ($search) {
                $q->where('vehicle_interest', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('contact', function (Builder $contactQuery) use ($search) {
                        $contactQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($this->filterSector !== 'all') {
            $query->whereHas('contact', fn (Builder $q) => $q->where('source', $this->filterSector));
        }

        if ($this->filterTag !== 'all') {
            $query->whereHas('contact', fn (Builder $q) => $q->whereJsonContains('tags', $this->filterTag));
        }

        if ($this->filterAgent === 'unassigned') {
            $query->whereNull('assigned_to');
        } elseif ($this->filterAgent !== 'all' && is_numeric($this->filterAgent)) {
            $query->where('assigned_to', (int) $this->filterAgent);
        }

        if ($this->filterChannel !== 'all') {
            $query->where('source', $this->filterChannel);
        }

        $periodStart = match ($this->filterPeriod) {
            'today' => now()->startOfDay(),
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            default => null,
        };

        if ($periodStart) {
            $query->where('created_at', '>=', $periodStart);
        }

        if ($this->sortOrder === 'oldest') {
            $query->orderBy('created_at')->orderBy('id');
        } else {
            $query->orderByDesc('updated_at')->orderByDesc('id');
        }

        return $query;
    }
}
