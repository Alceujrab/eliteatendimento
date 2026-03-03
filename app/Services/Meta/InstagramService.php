<?php

declare(strict_types=1);

namespace App\Services\Meta;

use App\Models\Channel;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Service for handling Instagram DM conversations.
 * Uses the same Meta Graph API (Messenger Platform) but with Instagram-specific fields.
 */
class InstagramService
{
    public function __construct(
        protected MetaGraphClient $client,
    ) {}

    /**
     * Process an incoming Instagram DM webhook event.
     */
    public function handleIncomingMessage(array $entry): void
    {
        foreach ($entry['messaging'] ?? [] as $event) {
            if (isset($event['message'])) {
                $this->processMessage($entry['id'], $event);
            } elseif (isset($event['postback'])) {
                $this->processStoryReply($entry['id'], $event);
            } elseif (isset($event['read'])) {
                $this->processRead($event);
            }
        }
    }

    /**
     * Process an incoming Instagram DM.
     */
    protected function processMessage(string $igAccountId, array $event): void
    {
        $senderId = $event['sender']['id'];
        $messageData = $event['message'];
        $timestamp = $event['timestamp'] ?? now()->getTimestampMs();

        // Skip echo messages (sent by us)
        if ($messageData['is_echo'] ?? false) {
            return;
        }

        // Find the channel by Instagram account ID
        $channel = Channel::where('type', 'instagram')
            ->where('identifier', $igAccountId)
            ->where('is_active', true)
            ->first();

        if (!$channel) {
            Log::warning('Meta Instagram: No active channel found', ['ig_account_id' => $igAccountId]);
            return;
        }

        // Find or create contact
        $contact = $this->findOrCreateContact($channel, $senderId);

        // Find or create conversation
        $conversation = $this->findOrCreateConversation($channel, $contact, $senderId);

        // Determine message type and content
        $type = 'text';
        $body = $messageData['text'] ?? null;
        $attachments = [];

        if (isset($messageData['attachments'])) {
            foreach ($messageData['attachments'] as $att) {
                $type = match ($att['type'] ?? 'fallback') {
                    'image' => 'image',
                    'video' => 'video',
                    'audio' => 'audio',
                    'share' => 'text', // Story shares
                    'ig_reel' => 'video',
                    default => 'text',
                };

                $attachments[] = [
                    'type' => $att['type'],
                    'url' => $att['payload']['url'] ?? null,
                    'filename' => 'instagram_' . ($att['type'] ?? 'file'),
                ];
            }
        }

        // Handle story replies
        if (isset($messageData['reply_to']['story'])) {
            $body = ($body ? $body . "\n\n" : '') . '📷 Resposta ao story: ' . ($messageData['reply_to']['story']['url'] ?? '');
        }

        // Handle story mentions
        if (isset($messageData['reply_to']['mid'])) {
            $body = $body ?? '💬 Resposta a mensagem anterior';
        }

        // Create the message
        Message::create([
            'conversation_id' => $conversation->id,
            'contact_id' => $contact->id,
            'user_id' => null,
            'type' => $type,
            'body' => $body,
            'attachments' => !empty($attachments) ? $attachments : null,
            'direction' => 'inbound',
            'status' => 'delivered',
            'external_id' => $messageData['mid'] ?? null,
            'metadata' => [
                'platform' => 'instagram',
                'sender_id' => $senderId,
                'timestamp' => $timestamp,
                'is_story_reply' => isset($messageData['reply_to']['story']),
            ],
        ]);

        // Update conversation
        $conversation->update([
            'last_message_preview' => Str::limit($body ?? '[Mídia]', 100),
            'last_message_at' => now(),
            'unread_count' => $conversation->unread_count + 1,
            'status' => $conversation->status === 'resolved' ? 'open' : $conversation->status,
        ]);
    }

    /**
     * Process Instagram story reply / postback.
     */
    protected function processStoryReply(string $igAccountId, array $event): void
    {
        // Story replies come as regular messages with reply_to.story
        // This handles button postbacks from ice_breakers or persistent_menu
        $senderId = $event['sender']['id'];
        $postback = $event['postback'];

        $channel = Channel::where('type', 'instagram')
            ->where('identifier', $igAccountId)
            ->where('is_active', true)
            ->first();

        if (!$channel) return;

        $contact = $this->findOrCreateContact($channel, $senderId);
        $conversation = $this->findOrCreateConversation($channel, $contact, $senderId);

        Message::create([
            'conversation_id' => $conversation->id,
            'contact_id' => $contact->id,
            'type' => 'text',
            'body' => $postback['title'] ?? $postback['payload'] ?? '[Ação Instagram]',
            'direction' => 'inbound',
            'status' => 'delivered',
            'metadata' => [
                'platform' => 'instagram',
                'type' => 'postback',
                'payload' => $postback['payload'] ?? null,
            ],
        ]);

        $conversation->update([
            'last_message_preview' => Str::limit($postback['title'] ?? '[Ação]', 100),
            'last_message_at' => now(),
            'unread_count' => $conversation->unread_count + 1,
        ]);
    }

    /**
     * Process read receipt.
     */
    protected function processRead(array $event): void
    {
        $watermark = $event['read']['watermark'] ?? 0;
        $senderId = $event['sender']['id'] ?? null;

        if ($senderId && $watermark) {
            Message::where('direction', 'outbound')
                ->where('status', 'delivered')
                ->whereHas('conversation', function ($q) use ($senderId) {
                    $q->where('channel_conversation_id', $senderId)
                      ->whereHas('channel', fn ($c) => $c->where('type', 'instagram'));
                })
                ->where('created_at', '<=', \Carbon\Carbon::createFromTimestampMs($watermark))
                ->update(['status' => 'read']);
        }
    }

    /**
     * Send a text message via Instagram DM.
     */
    public function sendTextMessage(Channel $channel, Conversation $conversation, string $text, ?int $userId = null): ?Message
    {
        $credentials = $channel->credentials ?? [];
        $pageId = $credentials['connected_page_id'] ?? $channel->identifier;
        $accessToken = $credentials['page_access_token'] ?? null;
        $recipientId = $conversation->channel_conversation_id;

        if (!$pageId || !$accessToken || !$recipientId) {
            Log::error('Meta Instagram: Missing credentials for sending', [
                'channel_id' => $channel->id,
            ]);
            return null;
        }

        // Instagram uses the same Send API but via the connected Facebook Page
        $result = $this->client->sendTextMessage($pageId, $accessToken, $recipientId, $text);

        if (!$result) return null;

        return Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $userId,
            'contact_id' => $conversation->contact_id,
            'type' => 'text',
            'body' => $text,
            'direction' => 'outbound',
            'status' => 'sent',
            'external_id' => $result['message_id'] ?? null,
            'metadata' => [
                'platform' => 'instagram',
                'recipient_id' => $result['recipient_id'] ?? $recipientId,
            ],
        ]);
    }

    /**
     * Send an image via Instagram DM.
     */
    public function sendImageMessage(Channel $channel, Conversation $conversation, string $imageUrl, ?int $userId = null): ?Message
    {
        $credentials = $channel->credentials ?? [];
        $pageId = $credentials['connected_page_id'] ?? $channel->identifier;
        $accessToken = $credentials['page_access_token'] ?? null;
        $recipientId = $conversation->channel_conversation_id;

        if (!$pageId || !$accessToken || !$recipientId) return null;

        $result = $this->client->sendImageMessage($pageId, $accessToken, $recipientId, $imageUrl);

        if (!$result) return null;

        return Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $userId,
            'contact_id' => $conversation->contact_id,
            'type' => 'image',
            'body' => null,
            'attachments' => [['type' => 'image', 'url' => $imageUrl]],
            'direction' => 'outbound',
            'status' => 'sent',
            'external_id' => $result['message_id'] ?? null,
            'metadata' => ['platform' => 'instagram'],
        ]);
    }

    /**
     * Find or create a contact from an Instagram sender.
     */
    protected function findOrCreateContact(Channel $channel, string $senderId): Contact
    {
        $existingConversation = Conversation::where('channel_id', $channel->id)
            ->where('channel_conversation_id', $senderId)
            ->first();

        if ($existingConversation) {
            return $existingConversation->contact;
        }

        // Try to get profile info from Meta
        $credentials = $channel->credentials ?? [];
        $accessToken = $credentials['page_access_token'] ?? null;
        $profile = null;

        if ($accessToken) {
            $profile = $this->client->getUserProfile($senderId, $accessToken, [
                'name', 'profile_pic', 'username',
            ]);
        }

        $name = $profile['name'] ?? 'Visitante Instagram #' . substr($senderId, -6);

        return Contact::create([
            'tenant_id' => $channel->tenant_id,
            'name' => $name,
            'avatar' => $profile['profile_pic'] ?? null,
            'source' => 'instagram',
            'custom_fields' => [
                'instagram_igsid' => $senderId,
                'instagram_username' => $profile['username'] ?? null,
            ],
        ]);
    }

    /**
     * Find or create a conversation for an Instagram DM.
     */
    protected function findOrCreateConversation(Channel $channel, Contact $contact, string $senderId): Conversation
    {
        $conversation = Conversation::where('channel_id', $channel->id)
            ->where('channel_conversation_id', $senderId)
            ->whereIn('status', ['new', 'open', 'pending'])
            ->first();

        if ($conversation) {
            return $conversation;
        }

        return Conversation::create([
            'tenant_id' => $channel->tenant_id,
            'contact_id' => $contact->id,
            'channel_id' => $channel->id,
            'channel_conversation_id' => $senderId,
            'status' => 'new',
            'priority' => 'medium',
            'last_message_at' => now(),
        ]);
    }
}
