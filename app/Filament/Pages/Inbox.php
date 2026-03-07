<?php

namespace App\Filament\Pages;

use App\Models\Channel;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\QuickReply;
use App\Models\User;
use App\Services\ChannelDispatcher;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;

class Inbox extends Page
{
    protected string $view = 'filament.pages.inbox';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = 'Caixa de Entrada';
    protected static ?string $title = 'Caixa de Entrada';
    protected static string | \UnitEnum | null $navigationGroup = 'Atendimento';
    protected static ?int $navigationSort = -1;

    /* ── Layout ── */

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    public function getHeading(): string|Htmlable|null
    {
        return '';
    }

    /* ── State ── */
    public ?int $activeConversationId = null;
    public string $messageText = '';
    public string $searchQuery = '';
    public string $filterStatus = 'active'; // active, mine, unassigned, all
    public string $filterChannel = 'all'; // all, whatsapp, instagram, facebook
    public string $filterAgent = 'all'; // all, me, unassigned, {id}
    public string $filterWhatsAppChannel = 'all'; // all, {channel_id}
    public bool $filterUnreadOnly = false;
    public bool $showQuickReplies = false;
    public bool $showContactInfo = false;
    public bool $isInternalNote = false;

    public function mount(): void
    {
        // Auto-select first conversation
        $first = $this->getConversations()->first();
        if ($first) {
            $this->activeConversationId = $first->id;
        }
    }

    /* ── Computed data ── */

    public function getConversations(): Collection
    {
        $tenant = filament()->getTenant();
        $query = Conversation::with(['contact', 'channel', 'assignedUser'])
            ->where('tenant_id', $tenant->id);

        // Filters
        match ($this->filterStatus) {
            'active' => $query->whereIn('status', ['new', 'open', 'pending']),
            'mine' => $query->where('assigned_to', Auth::id())->whereIn('status', ['new', 'open', 'pending']),
            'unassigned' => $query->whereNull('assigned_to')->whereIn('status', ['new', 'open', 'pending']),
            'new' => $query->where('status', 'new'),
            'open' => $query->where('status', 'open'),
            'pending' => $query->where('status', 'pending'),
            'resolved' => $query->where('status', 'resolved'),
            default => $query,
        };

        // Search
        if ($this->searchQuery) {
            $search = $this->searchQuery;
            $query->where(function ($q) use ($search) {
                $q->where('last_message_preview', 'like', "%{$search}%")
                  ->orWhereHas('contact', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        // Channel filter (omnichannel)
        match ($this->filterChannel) {
            'whatsapp' => $query->whereHas('channel', fn ($q) => $q->whereIn('type', ['whatsapp_meta', 'whatsapp_evolution'])),
            'instagram' => $query->whereHas('channel', fn ($q) => $q->where('type', 'instagram')),
            'facebook' => $query->whereHas('channel', fn ($q) => $q->where('type', 'facebook')),
            default => $query,
        };

        // Agent filter (multiusuário)
        if ($this->filterAgent === 'me') {
            $query->where('assigned_to', Auth::id());
        } elseif ($this->filterAgent === 'unassigned') {
            $query->whereNull('assigned_to');
        } elseif ($this->filterAgent !== 'all' && is_numeric($this->filterAgent)) {
            $query->where('assigned_to', (int) $this->filterAgent);
        }

        // WhatsApp instance filter (multi-whatsapp)
        if ($this->filterWhatsAppChannel !== 'all' && is_numeric($this->filterWhatsAppChannel)) {
            $query->where('channel_id', (int) $this->filterWhatsAppChannel);
        }

        if ($this->filterUnreadOnly) {
            $query->where('unread_count', '>', 0);
        }

        return $query->orderByDesc('last_message_at')->limit(100)->get();
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
    public function whatsappChannels(): Collection
    {
        $tenant = filament()->getTenant();

        return Channel::query()
            ->where('tenant_id', $tenant->id)
            ->whereIn('type', ['whatsapp_meta', 'whatsapp_evolution'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'type']);
    }

    #[Computed]
    public function inboxStats(): array
    {
        $tenant = filament()->getTenant();

        $base = Conversation::query()->where('tenant_id', $tenant->id);

        return [
            'active' => (clone $base)->whereIn('status', ['new', 'open', 'pending'])->count(),
            'mine' => (clone $base)->where('assigned_to', Auth::id())->whereIn('status', ['new', 'open', 'pending'])->count(),
            'unassigned' => (clone $base)->whereNull('assigned_to')->whereIn('status', ['new', 'open', 'pending'])->count(),
            'unread' => (clone $base)->where('unread_count', '>', 0)->count(),
        ];
    }

    #[Computed]
    public function queueStats(): array
    {
        $tenant = filament()->getTenant();

        $base = Conversation::query()->where('tenant_id', $tenant->id);

        return [
            'new' => (clone $base)->where('status', 'new')->count(),
            'open' => (clone $base)->where('status', 'open')->count(),
            'pending' => (clone $base)->where('status', 'pending')->count(),
            'resolved' => (clone $base)->where('status', 'resolved')->count(),
        ];
    }

    #[Computed]
    public function slaStats(): array
    {
        $tenant = filament()->getTenant();
        $now = Carbon::now();

        $firstResponseOverdue = Conversation::query()
            ->where('tenant_id', $tenant->id)
            ->whereIn('status', ['new', 'open'])
            ->whereNull('first_response_at')
            ->where('created_at', '<=', $now->copy()->subMinutes(5))
            ->count();

        $pendingOverdue = Conversation::query()
            ->where('tenant_id', $tenant->id)
            ->where('status', 'pending')
            ->where('updated_at', '<=', $now->copy()->subMinutes(30))
            ->count();

        return [
            'first_response_overdue' => $firstResponseOverdue,
            'pending_overdue' => $pendingOverdue,
        ];
    }

    #[Computed]
    public function activeConversation(): ?Conversation
    {
        if (!$this->activeConversationId) return null;

        return Conversation::with(['contact', 'channel', 'assignedUser'])
            ->find($this->activeConversationId);
    }

    #[Computed]
    public function messages(): Collection
    {
        if (!$this->activeConversationId) return collect();

        return Message::with(['user', 'contact'])
            ->where('conversation_id', $this->activeConversationId)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    #[Computed]
    public function quickReplies(): Collection
    {
        $tenant = filament()->getTenant();
        return QuickReply::where('tenant_id', $tenant->id)
            ->where(function ($q) {
                $q->where('is_global', true)
                                    ->orWhere('user_id', Auth::id());
            })
            ->orderBy('title')
            ->get();
    }

    /* ── Actions ── */

    public function selectConversation(int $id): void
    {
        $this->activeConversationId = $id;
        $this->showQuickReplies = false;
        $this->showContactInfo = false;
        $this->isInternalNote = false;
        $this->messageText = '';

        // Mark as read
        Conversation::where('id', $id)->update(['unread_count' => 0]);
    }

    public function sendMessage(): void
    {
        if (!$this->activeConversationId || trim($this->messageText) === '') return;

        $conversation = Conversation::with('channel')->find($this->activeConversationId);
        if (!$conversation) return;

        $text = $this->messageText;
        $userId = Auth::id();

        // --- Dispatch via external channel if applicable ---
        $message = null;

        if (!$this->isInternalNote && $conversation->channel) {
            /** @var ChannelDispatcher $dispatcher */
            $dispatcher = app(ChannelDispatcher::class);

            if ($dispatcher->supportsExternalDispatch($conversation->channel->type)) {
                $message = $dispatcher->sendText($conversation, $text, $userId);
            }
        }

        // Fallback: create local-only message (webchat, email, internal notes, or dispatch failure)
        if (!$message) {
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'user_id'         => $userId,
                'contact_id'      => $this->isInternalNote ? null : $conversation->contact_id,
                'type'            => 'text',
                'body'            => $text,
                'direction'       => $this->isInternalNote ? 'outbound' : 'outbound',
                'status'          => 'sent',
                'is_internal_note' => $this->isInternalNote,
            ]);
        }

        $conversation->update([
            'last_message_preview' => \Illuminate\Support\Str::limit($text, 100),
            'last_message_at'      => now(),
            'status'               => $conversation->status === 'new' ? 'open' : $conversation->status,
        ]);

        if (!$conversation->first_response_at && !$this->isInternalNote) {
            $conversation->update(['first_response_at' => now()]);
        }

        $this->messageText = '';
        $this->isInternalNote = false;
        $this->showQuickReplies = false;

        $this->dispatch('message-sent');
    }

    public function insertQuickReply(int $replyId): void
    {
        $reply = QuickReply::find($replyId);
        if ($reply) {
            $this->messageText = $reply->body;
            $this->showQuickReplies = false;
        }
    }

    public function assignToMe(): void
    {
        if (!$this->activeConversationId) return;

        Conversation::where('id', $this->activeConversationId)
            ->update([
                'assigned_to' => Auth::id(),
                'status' => 'open',
            ]);
    }

    public function transferConversation(int $userId): void
    {
        if (! $this->activeConversationId) {
            return;
        }

        Conversation::where('id', $this->activeConversationId)
            ->update([
                'assigned_to' => $userId,
                'status' => 'open',
            ]);
    }

    public function resolveConversation(): void
    {
        if (!$this->activeConversationId) return;

        Conversation::where('id', $this->activeConversationId)
            ->update([
                'status' => 'resolved',
                'resolved_at' => now(),
            ]);

        // Move to next conversation
        $next = $this->getConversations()->first();
        $this->activeConversationId = $next?->id;
    }

    public function reopenConversation(): void
    {
        if (!$this->activeConversationId) return;

        Conversation::where('id', $this->activeConversationId)
            ->update([
                'status' => 'open',
                'resolved_at' => null,
            ]);
    }

    public function toggleContactInfo(): void
    {
        $this->showContactInfo = !$this->showContactInfo;
    }

    /* ── Navigation badge ── */

    public static function getNavigationBadge(): ?string
    {
        try {
            $tenant = filament()->getTenant();
            if (!$tenant) return null;

            $count = Conversation::where('tenant_id', $tenant->id)
                ->whereIn('status', ['new', 'open', 'pending'])
                ->where('unread_count', '>', 0)
                ->count();

            return $count > 0 ? (string) $count : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public static function getNavigationBadgeColor(): string | array | null
    {
        return 'success';
    }

    /* ── Polling ── */

    #[On('refresh-inbox')]
    public function refreshInbox(): void
    {
        // Livewire will re-render
    }
}
