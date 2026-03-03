<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Lead;
use App\Models\Ticket;
use App\Models\Contact;
use App\Models\Campaign;
use App\Models\SatisfactionSurvey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $userId = auth()->id();
        $isGestor = auth()->user()->isGestor();

        // KPIs principais
        $newLeadsToday = Lead::where('tenant_id', $tenantId)
            ->whereDate('created_at', today())
            ->count();

        $activeConversations = Conversation::where('tenant_id', $tenantId)
            ->active()
            ->when(!$isGestor, fn($q) => $q->forUser($userId))
            ->count();

        $openTickets = Ticket::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['resolved', 'closed'])
            ->when(!$isGestor, fn($q) => $q->where('assigned_to', $userId))
            ->count();

        $avgResponseTime = Conversation::where('tenant_id', $tenantId)
            ->whereNotNull('first_response_at')
            ->whereDate('created_at', '>=', now()->subDays(7))
            ->selectRaw('AVG((julianday(first_response_at) - julianday(created_at)) * 1440) as avg_time')
            ->value('avg_time') ?? 0;

        // NPS médio últimos 30 dias
        $avgNps = SatisfactionSurvey::where('tenant_id', $tenantId)
            ->where('type', 'nps')
            ->where('created_at', '>=', now()->subDays(30))
            ->avg('score') ?? 0;

        // Conversas por canal (últimos 7 dias)
        $conversationsByChannel = Conversation::where('conversations.tenant_id', $tenantId)
            ->join('channels', 'conversations.channel_id', '=', 'channels.id')
            ->where('conversations.created_at', '>=', now()->subDays(7))
            ->select('channels.type', 'channels.name', DB::raw('count(*) as total'))
            ->groupBy('channels.type', 'channels.name')
            ->get();

        // Volume por dia (últimos 7 dias)
        $dailyVolume = Conversation::where('tenant_id', $tenantId)
            ->where('created_at', '>=', now()->subDays(7))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Leads por estágio
        $leadsByStage = Lead::where('tenant_id', $tenantId)
            ->whereIn('stage', Lead::activeStages())
            ->select('stage', DB::raw('count(*) as total'))
            ->groupBy('stage')
            ->pluck('total', 'stage');

        // Conversas recentes sem atribuição
        $unassignedConversations = Conversation::with(['contact', 'channel'])
            ->where('tenant_id', $tenantId)
            ->active()
            ->unassigned()
            ->latest('last_message_at')
            ->limit(5)
            ->get();

        // Tickets com SLA próximo de vencer
        $slaWarningTickets = Ticket::with(['contact', 'assignedUser'])
            ->where('tenant_id', $tenantId)
            ->whereNotIn('status', ['resolved', 'closed'])
            ->whereNotNull('due_at')
            ->where('due_at', '<=', now()->addHours(2))
            ->orderBy('due_at')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'newLeadsToday', 'activeConversations', 'openTickets', 'avgResponseTime',
            'avgNps', 'conversationsByChannel', 'dailyVolume', 'leadsByStage',
            'unassignedConversations', 'slaWarningTickets'
        ));
    }
}
