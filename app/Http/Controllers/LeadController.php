<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Contact;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $isGestor = auth()->user()->isGestor();

        $query = Lead::with(['contact', 'assignedUser'])
            ->where('tenant_id', $tenantId);

        if (!$isGestor) {
            $query->where('assigned_to', auth()->id());
        }

        if ($request->filled('stage')) {
            $query->where('stage', $request->stage);
        } else {
            $query->whereIn('stage', Lead::activeStages());
        }

        if ($request->filled('temperature')) {
            $query->where('temperature', $request->temperature);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('contact', fn($q) => $q->where('name', 'like', "%{$search}%"));
        }

        $leads = $query->latest()->get();

        // Agrupar por estágio para Kanban
        $stages = [];
        foreach (Lead::activeStages() as $stage) {
            $stages[$stage] = $leads->where('stage', $stage)->values();
        }

        $agents = \App\Models\User::where('tenant_id', $tenantId)->where('is_active', true)->get();

        return view('leads.index', compact('leads', 'stages', 'agents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'vehicle_interest' => 'nullable|string|max:255',
            'estimated_value' => 'nullable|numeric',
            'source' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $data['tenant_id'] = auth()->user()->tenant_id;
        $data['assigned_to'] = auth()->id();
        $data['stage'] = 'new';

        $lead = Lead::create($data);

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'type' => 'note',
            'description' => 'Lead criado.',
        ]);

        return redirect()->route('leads.index')->with('success', 'Lead criado com sucesso.');
    }

    public function show(Lead $lead)
    {
        $lead->load(['contact', 'assignedUser', 'conversation', 'activities.user']);

        $vehicles = \App\Models\Vehicle::where('tenant_id', auth()->user()->tenant_id)
            ->available()->limit(10)->get();

        return view('leads.show', compact('lead', 'vehicles'));
    }

    public function updateStage(Request $request, Lead $lead)
    {
        $request->validate(['stage' => 'required|in:' . implode(',', Lead::stages())]);

        $oldStage = $lead->stage;
        $lead->update(['stage' => $request->stage]);

        if ($request->stage === 'won') {
            $lead->update(['won_at' => now()]);
        } elseif ($request->stage === 'lost') {
            $lead->update(['lost_at' => now(), 'lost_reason' => $request->lost_reason]);
        }

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'type' => 'stage_change',
            'description' => "Estágio alterado de {$oldStage} para {$request->stage}.",
        ]);

        return redirect()->back()->with('success', 'Estágio atualizado.');
    }

    public function addActivity(Request $request, Lead $lead)
    {
        $data = $request->validate([
            'type' => 'required|in:note,call,email,whatsapp,meeting,follow_up',
            'description' => 'required|string|max:1000',
            'scheduled_at' => 'nullable|date',
        ]);

        $data['lead_id'] = $lead->id;
        $data['user_id'] = auth()->id();

        LeadActivity::create($data);

        if ($data['type'] === 'follow_up' && isset($data['scheduled_at'])) {
            $lead->update(['next_follow_up' => $data['scheduled_at']]);
        }

        return redirect()->back()->with('success', 'Atividade registrada.');
    }
}
