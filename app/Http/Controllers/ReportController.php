<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Lead;
use App\Models\Ticket;
use App\Models\Contact;
use App\Models\SatisfactionSurvey;
use App\Models\Campaign;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $period = $request->get('period', '30');
        $startDate = Carbon::now()->subDays($period)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        // ------ Conversations ------
        $conversationStats = [
            'total' => Conversation::where('tenant_id', $tenantId)
                ->whereBetween('created_at', [$startDate, $endDate])->count(),
            'resolved' => Conversation::where('tenant_id', $tenantId)
                ->where('status', 'resolved')
                ->whereBetween('created_at', [$startDate, $endDate])->count(),
            'avgResponseTime' => Conversation::where('tenant_id', $tenantId)
                ->whereNotNull('first_response_at')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('AVG((julianday(first_response_at) - julianday(created_at)) * 1440) as avg_minutes')
                ->value('avg_minutes') ?? 0,
        ];

        $conversationsByChannel = Conversation::where('conversations.tenant_id', $tenantId)
            ->join('channels', 'conversations.channel_id', '=', 'channels.id')
            ->whereBetween('conversations.created_at', [$startDate, $endDate])
            ->selectRaw('channels.type, count(*) as total')
            ->groupBy('channels.type')
            ->pluck('total', 'type');

        $conversationsByDay = Conversation::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, count(*) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        // ------ Leads ------
        $leadStats = [
            'total' => Lead::where('tenant_id', $tenantId)
                ->whereBetween('created_at', [$startDate, $endDate])->count(),
            'won' => Lead::where('tenant_id', $tenantId)
                ->where('stage', 'won')
                ->whereBetween('created_at', [$startDate, $endDate])->count(),
            'lost' => Lead::where('tenant_id', $tenantId)
                ->where('stage', 'lost')
                ->whereBetween('created_at', [$startDate, $endDate])->count(),
            'totalValue' => Lead::where('tenant_id', $tenantId)
                ->where('stage', 'won')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('estimated_value'),
        ];

        $leadsByStage = Lead::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('stage, count(*) as total')
            ->groupBy('stage')
            ->pluck('total', 'stage');

        $leadsBySource = Lead::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('source, count(*) as total')
            ->groupBy('source')
            ->pluck('total', 'source');

        // ------ Tickets ------
        $ticketStats = [
            'total' => Ticket::where('tenant_id', $tenantId)
                ->whereBetween('created_at', [$startDate, $endDate])->count(),
            'resolved' => Ticket::where('tenant_id', $tenantId)
                ->where('status', 'resolved')
                ->whereBetween('created_at', [$startDate, $endDate])->count(),
            'slaBreached' => Ticket::where('tenant_id', $tenantId)
                ->whereNotNull('due_at')
                ->whereColumn('resolved_at', '>', 'due_at')
                ->whereBetween('created_at', [$startDate, $endDate])->count(),
        ];

        $ticketsByCategory = Ticket::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('category, count(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        // ------ NPS / CSAT ------
        $npsData = SatisfactionSurvey::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate, $endDate]);

        $npsScores = (clone $npsData)->whereNotNull('nps_score')->pluck('nps_score');
        $nps = $this->calculateNps($npsScores);

        $csatScores = (clone $npsData)->whereNotNull('csat_score')->pluck('csat_score');
        $csat = $csatScores->count() > 0 ? round($csatScores->avg(), 1) : null;

        // ------ Agent Performance ------
        $agentPerformance = Conversation::where('conversations.tenant_id', $tenantId)
            ->join('users', 'conversations.assigned_to', '=', 'users.id')
            ->whereBetween('conversations.created_at', [$startDate, $endDate])
            ->whereNotNull('conversations.assigned_to')
            ->selectRaw('users.name, count(*) as total_conversations, 
                SUM(CASE WHEN conversations.status = \'resolved\' THEN 1 ELSE 0 END) as resolved')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_conversations')
            ->get();

        // ------ Campaigns ------
        $campaignStats = Campaign::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw("count(*) as total, 
                SUM(total_recipients) as total_sent,
                SUM(delivered_count) as total_delivered,
                SUM(read_count) as total_read")
            ->first();

        return view('reports.index', compact(
            'period', 'startDate', 'endDate',
            'conversationStats', 'conversationsByChannel', 'conversationsByDay',
            'leadStats', 'leadsByStage', 'leadsBySource',
            'ticketStats', 'ticketsByCategory',
            'nps', 'csat',
            'agentPerformance',
            'campaignStats'
        ));
    }

    private function calculateNps($scores)
    {
        if ($scores->isEmpty()) return null;

        $total = $scores->count();
        $promoters = $scores->filter(fn($s) => $s >= 9)->count();
        $detractors = $scores->filter(fn($s) => $s <= 6)->count();

        return round((($promoters - $detractors) / $total) * 100);
    }
}
