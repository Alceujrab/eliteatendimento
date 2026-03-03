<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Contact;
use App\Models\QuickReply;
use Illuminate\Http\Request;

class InboxController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $userId = auth()->id();
        $isGestor = auth()->user()->isGestor();

        $query = Conversation::with(['contact', 'channel', 'assignedUser', 'lastMessage'])
            ->where('tenant_id', $tenantId);

        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->active();
        }

        if ($request->filled('channel')) {
            $query->where('channel_id', $request->channel);
        }

        if ($request->filled('assigned')) {
            if ($request->assigned === 'me') {
                $query->where('assigned_to', $userId);
            } elseif ($request->assigned === 'unassigned') {
                $query->unassigned();
            } else {
                $query->where('assigned_to', $request->assigned);
            }
        } elseif (!$isGestor) {
            $query->where(function ($q) use ($userId) {
                $q->where('assigned_to', $userId)->orWhereNull('assigned_to');
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('contact', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $conversations = $query->latest('last_message_at')->paginate(50);

        // Conversa selecionada
        $activeConversation = null;
        $messages = collect();
        $quickReplies = collect();

        if ($request->filled('conversation')) {
            $activeConversation = Conversation::with(['contact', 'channel', 'assignedUser'])
                ->where('tenant_id', $tenantId)
                ->find($request->conversation);

            if ($activeConversation) {
                $messages = Message::where('conversation_id', $activeConversation->id)
                    ->with(['user', 'contact'])
                    ->orderBy('created_at')
                    ->get();

                // Marcar como lido
                $activeConversation->update(['unread_count' => 0]);
            }

            $quickReplies = QuickReply::where('tenant_id', $tenantId)
                ->where(function ($q) use ($userId) {
                    $q->where('is_global', true)->orWhere('user_id', $userId);
                })
                ->orderBy('title')
                ->get();
        }

        $channels = \App\Models\Channel::where('tenant_id', $tenantId)->where('is_active', true)->get();
        $agents = \App\Models\User::where('tenant_id', $tenantId)->where('is_active', true)->get();

        return view('inbox.index', compact(
            'conversations', 'activeConversation', 'messages',
            'quickReplies', 'channels', 'agents'
        ));
    }

    public function sendMessage(Request $request, Conversation $conversation)
    {
        $request->validate([
            'body' => 'required_without:attachments|string|max:4096',
            'type' => 'sometimes|string|in:text,image,document,audio,video',
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => auth()->id(),
            'type' => $request->type ?? 'text',
            'body' => $request->body,
            'direction' => 'outbound',
            'status' => 'sent',
            'is_internal_note' => $request->boolean('is_note'),
        ]);

        $conversation->update([
            'last_message_preview' => mb_substr($request->body, 0, 100),
            'last_message_at' => now(),
            'status' => $conversation->status === 'new' ? 'open' : $conversation->status,
        ]);

        if (!$conversation->first_response_at && !$request->boolean('is_note')) {
            $conversation->update(['first_response_at' => now()]);
        }

        // TODO: Integrar com API do canal para envio real (Meta, Evolution, etc.)

        return redirect()->route('inbox', ['conversation' => $conversation->id])
            ->with('success', 'Mensagem enviada.');
    }

    public function assign(Request $request, Conversation $conversation)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        $conversation->update([
            'assigned_to' => $request->user_id,
            'status' => 'open',
        ]);

        return redirect()->route('inbox', ['conversation' => $conversation->id])
            ->with('success', 'Conversa atribuída.');
    }

    public function updateStatus(Request $request, Conversation $conversation)
    {
        $request->validate(['status' => 'required|in:new,open,pending,resolved,archived']);

        $updates = ['status' => $request->status];
        if ($request->status === 'resolved') {
            $updates['resolved_at'] = now();
        }

        $conversation->update($updates);

        return redirect()->route('inbox', ['conversation' => $conversation->id])
            ->with('success', 'Status atualizado.');
    }
}
