<?php

namespace App\Filament\Widgets;

use App\Models\Conversation;
use App\Models\Lead;
use App\Models\Ticket;
use App\Models\Contact;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected string | null $pollingInterval = '30s';

    protected function getStats(): array
    {
        $tenant = filament()->getTenant();

        $activeConversations = Conversation::where('tenant_id', $tenant->id)
            ->whereIn('status', ['new', 'open', 'pending'])->count();
        $newConversationsToday = Conversation::where('tenant_id', $tenant->id)
            ->where('status', 'new')->whereDate('created_at', today())->count();

        $activeLeads = Lead::where('tenant_id', $tenant->id)
            ->whereIn('stage', Lead::activeStages())->count();
        $leadsValue = Lead::where('tenant_id', $tenant->id)
            ->whereIn('stage', Lead::activeStages())->sum('estimated_value');

        $openTickets = Ticket::where('tenant_id', $tenant->id)
            ->whereIn('status', ['open', 'in_progress'])->count();
        $overdueTickets = Ticket::where('tenant_id', $tenant->id)
            ->whereIn('status', ['open', 'in_progress'])
            ->where('due_at', '<', now())->count();

        $contactsThisMonth = Contact::where('tenant_id', $tenant->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)->count();

        return [
            Stat::make('Conversas Ativas', $activeConversations)
                ->description($newConversationsToday . ' novas hoje')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('success')
                ->chart([7, 3, 4, 5, 6, $activeConversations]),

            Stat::make('Leads Ativos', $activeLeads)
                ->description('R$ ' . number_format($leadsValue, 0, ',', '.') . ' em negociação')
                ->descriptionIcon('heroicon-m-fire')
                ->color('warning')
                ->chart([5, 8, 3, 7, 4, $activeLeads]),

            Stat::make('Tickets Abertos', $openTickets)
                ->description($overdueTickets > 0 ? $overdueTickets . ' vencido(s)' : 'Nenhum vencido')
                ->descriptionIcon('heroicon-m-ticket')
                ->color($overdueTickets > 0 ? 'danger' : 'info')
                ->chart([3, 5, 2, 4, 6, $openTickets]),

            Stat::make('Novos Contatos (mês)', $contactsThisMonth)
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->chart([4, 6, 8, 3, 5, $contactsThisMonth]),
        ];
    }
}
