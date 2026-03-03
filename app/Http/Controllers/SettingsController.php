<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Channel;
use App\Models\SlaPolicy;
use App\Models\Automation;
use App\Models\QuickReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function index()
    {
        $tenant = auth()->user()->tenant;

        return view('settings.index', compact('tenant'));
    }

    public function updateTenant(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'cnpj' => 'nullable|string|max:18',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'business_hours' => 'nullable|array',
            'settings' => 'nullable|array',
        ]);

        auth()->user()->tenant->update($data);

        return back()->with('success', 'Configurações atualizadas.');
    }

    // ---- Users ----
    public function users()
    {
        $users = User::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name')
            ->get();

        return view('settings.users', compact('users'));
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,gestor,vendedor,atendente',
            'phone' => 'nullable|string|max:20',
            'max_concurrent_chats' => 'nullable|integer|min:1|max:20',
        ]);

        $data['tenant_id'] = auth()->user()->tenant_id;
        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = true;

        User::create($data);

        return back()->with('success', 'Usuário criado.');
    }

    public function updateUser(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,gestor,vendedor,atendente',
            'phone' => 'nullable|string|max:20',
            'max_concurrent_chats' => 'nullable|integer|min:1|max:20',
            'is_active' => 'boolean',
        ]);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return back()->with('success', 'Usuário atualizado.');
    }

    // ---- Channels ----
    public function channels()
    {
        $channels = Channel::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name')
            ->get();

        return view('settings.channels', compact('channels'));
    }

    public function storeChannel(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:whatsapp_meta,whatsapp_evolution,facebook,instagram,telegram,email,webchat,sms',
            'credentials' => 'required|array',
            'is_active' => 'boolean',
        ]);

        $data['tenant_id'] = auth()->user()->tenant_id;

        Channel::create($data);

        return back()->with('success', 'Canal criado.');
    }

    public function updateChannel(Request $request, Channel $channel)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'credentials' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $channel->update($data);

        return back()->with('success', 'Canal atualizado.');
    }

    // ---- SLA Policies ----
    public function slaPolicies()
    {
        $policies = SlaPolicy::where('tenant_id', auth()->user()->tenant_id)->get();

        return view('settings.sla', compact('policies'));
    }

    public function storeSlaPolicy(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'first_response_minutes' => 'required|integer|min:1',
            'resolution_minutes' => 'required|integer|min:1',
        ]);

        $data['tenant_id'] = auth()->user()->tenant_id;

        SlaPolicy::create($data);

        return back()->with('success', 'Política SLA criada.');
    }

    // ---- Quick Replies ----
    public function quickReplies()
    {
        $replies = QuickReply::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('category')
            ->orderBy('title')
            ->get();

        return view('settings.quick-replies', compact('replies'));
    }

    public function storeQuickReply(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'shortcut' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:100',
            'is_global' => 'boolean',
        ]);

        $data['tenant_id'] = auth()->user()->tenant_id;
        $data['created_by'] = auth()->id();

        QuickReply::create($data);

        return back()->with('success', 'Resposta rápida criada.');
    }

    // ---- Automations ----
    public function automations()
    {
        $automations = Automation::where('tenant_id', auth()->user()->tenant_id)->get();

        return view('settings.automations', compact('automations'));
    }

    public function storeAutomation(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'trigger_type' => 'required|string|max:100',
            'trigger_conditions' => 'nullable|array',
            'actions' => 'nullable|array',
            'n8n_workflow_id' => 'nullable|string|max:100',
            'n8n_webhook_url' => 'nullable|url|max:500',
            'is_active' => 'boolean',
        ]);

        $data['tenant_id'] = auth()->user()->tenant_id;

        Automation::create($data);

        return back()->with('success', 'Automação criada.');
    }

    public function toggleAutomation(Automation $automation)
    {
        $automation->update(['is_active' => !$automation->is_active]);

        return back()->with('success', 'Automação ' . ($automation->is_active ? 'ativada' : 'desativada') . '.');
    }
}
