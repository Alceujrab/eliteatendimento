<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\SlaPolicy;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $isGestor = auth()->user()->isGestor();

        $query = Ticket::with(['contact', 'assignedUser'])
            ->where('tenant_id', $tenantId);

        if (!$isGestor) {
            $query->where('assigned_to', auth()->id());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhereHas('contact', fn($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        $tickets = $query->latest()->paginate(20);

        // Contadores por status
        $baseQuery = Ticket::where('tenant_id', $tenantId)
            ->when(!$isGestor, fn($q) => $q->where('assigned_to', auth()->id()));
        $statusCounts = [
            'open' => (clone $baseQuery)->where('status', 'open')->count(),
            'in_progress' => (clone $baseQuery)->where('status', 'in_progress')->count(),
            'waiting' => (clone $baseQuery)->where('status', 'waiting')->count(),
            'overdue' => (clone $baseQuery)->whereNotIn('status', ['resolved', 'closed'])
                ->whereNotNull('due_at')->where('due_at', '<', now())->count(),
        ];

        return view('tickets.index', compact('tickets', 'statusCounts'));
    }

    public function create()
    {
        $tenantId = auth()->user()->tenant_id;
        $contacts = \App\Models\Contact::where('tenant_id', $tenantId)->orderBy('name')->get();
        $agents = \App\Models\User::where('tenant_id', $tenantId)->where('is_active', true)->get();

        return view('tickets.create', compact('contacts', 'agents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:duvida,reclamacao,solicitacao,pos_venda,financeiro',
            'priority' => 'required|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $data['tenant_id'] = auth()->user()->tenant_id;

        // Calcular SLA
        $sla = SlaPolicy::where('tenant_id', $data['tenant_id'])
            ->where(function ($q) use ($data) {
                $q->where('category', $data['category'])
                  ->orWhere('priority', $data['priority'])
                  ->orWhere('is_default', true);
            })
            ->where('is_active', true)
            ->first();

        if ($sla) {
            $data['due_at'] = now()->addMinutes($sla->resolution_minutes);
        }

        $ticket = Ticket::create($data);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket criado: ' . $ticket->number);
    }

    public function show(Ticket $ticket)
    {
        $ticket->load(['contact', 'assignedUser', 'comments.user', 'conversation']);
        $agents = \App\Models\User::where('tenant_id', auth()->user()->tenant_id)->where('is_active', true)->get();

        return view('tickets.show', compact('ticket', 'agents'));
    }

    public function addComment(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
            'body' => 'required|string|max:5000',
            'is_internal' => 'sometimes|boolean',
        ]);

        TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'body' => $data['body'],
            'is_internal' => $request->boolean('is_internal'),
        ]);

        if (!$ticket->first_response_at) {
            $ticket->update(['first_response_at' => now()]);
        }

        return redirect()->back()->with('success', 'Comentário adicionado.');
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        $request->validate(['status' => 'required|in:open,in_progress,waiting,resolved,closed']);

        $updates = ['status' => $request->status];
        if ($request->status === 'resolved') $updates['resolved_at'] = now();
        if ($request->status === 'closed') $updates['closed_at'] = now();

        $ticket->update($updates);

        return redirect()->back()->with('success', 'Status atualizado.');
    }
}
