<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignMessage;
use App\Models\Contact;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $query = Campaign::where('tenant_id', $tenantId)->withCount('messages');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $campaigns = $query->latest()->paginate(15);

        $statusCounts = Campaign::where('tenant_id', $tenantId)
            ->selectRaw("status, count(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('campaigns.index', compact('campaigns', 'statusCounts'));
    }

    public function create()
    {
        $tenantId = auth()->user()->tenant_id;
        $contactCount = Contact::where('tenant_id', $tenantId)->count();

        return view('campaigns.create', compact('contactCount'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:whatsapp,sms,email',
            'message_template' => 'required|string',
            'audience_filter' => 'nullable|array',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        $data['tenant_id'] = auth()->user()->tenant_id;
        $data['created_by'] = auth()->id();
        $data['status'] = 'draft';
        $data['audience_filter'] = $data['audience_filter'] ?? [];

        $campaign = Campaign::create($data);

        return redirect()->route('campaigns.show', $campaign)->with('success', 'Campanha criada.');
    }

    public function show(Campaign $campaign)
    {
        $campaign->load('messages.contact');

        $stats = [
            'total' => $campaign->messages()->count(),
            'sent' => $campaign->messages()->where('status', 'sent')->count(),
            'delivered' => $campaign->messages()->where('status', 'delivered')->count(),
            'read' => $campaign->messages()->where('status', 'read')->count(),
            'failed' => $campaign->messages()->where('status', 'failed')->count(),
        ];

        return view('campaigns.show', compact('campaign', 'stats'));
    }

    public function send(Campaign $campaign)
    {
        if ($campaign->status !== 'draft' && $campaign->status !== 'paused') {
            return back()->with('error', 'Campanha não pode ser enviada neste estado.');
        }

        $tenantId = auth()->user()->tenant_id;

        // Build audience based on filters
        $contacts = Contact::where('tenant_id', $tenantId);

        if (!empty($campaign->audience_filter)) {
            if (isset($campaign->audience_filter['source'])) {
                $contacts->where('source', $campaign->audience_filter['source']);
            }
            if (isset($campaign->audience_filter['city'])) {
                $contacts->where('city', $campaign->audience_filter['city']);
            }
        }

        $contacts = $contacts->whereNotNull('phone')->get();

        foreach ($contacts as $contact) {
            CampaignMessage::firstOrCreate([
                'campaign_id' => $campaign->id,
                'contact_id' => $contact->id,
            ], [
                'status' => 'pending',
            ]);
        }

        $campaign->update([
            'status' => 'running',
            'started_at' => now(),
            'total_recipients' => $contacts->count(),
        ]);

        // TODO: Dispatch job to actually send messages via WhatsApp/SMS/Email API

        return back()->with('success', 'Campanha iniciada com ' . $contacts->count() . ' destinatários.');
    }

    public function pause(Campaign $campaign)
    {
        if ($campaign->status !== 'running') {
            return back()->with('error', 'Campanha não está em execução.');
        }

        $campaign->update(['status' => 'paused']);

        return back()->with('success', 'Campanha pausada.');
    }

    public function cancel(Campaign $campaign)
    {
        if (in_array($campaign->status, ['completed', 'cancelled'])) {
            return back()->with('error', 'Campanha já finalizada.');
        }

        $campaign->update(['status' => 'cancelled']);

        return back()->with('success', 'Campanha cancelada.');
    }
}
