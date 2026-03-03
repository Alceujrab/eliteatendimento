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
 * Service for handling Facebook Messenger conversations.
 * Uses the Meta Send API / Webhooks.
 */
class MessengerService
{
    public function __construct(
        protected MetaGraphClient $client,
    ) {}

    /**
     * Process an incoming Messenger webhook event.
     */
    public function handleIncomingMessage(array $entry): void
    {
        foreach ($entry['messaging'] ?? [] as $event) {
            if (isset($event['message'])) {
                $this->processMessage($entry['id'], $event);
            } elseif (isset($event['postback'])) {
                $this->processPostback($entry['id'], $event);
            } elseif (isset($event['delivery'])) {
                $this->processDelivery($event);
            } elseif (isset($event['read'])) {
                $this->processRead($event);
            }
        }
    }

    /**
     * Process an incoming message event.
     */
    protected function processMessage(string $pageId, array $event): void
    {
        $senderId = $event['sender']['id'];
        $messageData = $event['message'];
        $timestamp = $event['timestamp'] ?? now()->getTimestampMs();

        // Find the channel by page_id
        $channel = Channel::where('type', 'facebook')
            ->where('identifier', $pageId)
            ->where('is_active', true)
            ->first();

        if (!$channel) {
            Log::warning('Meta Messenger: No active channel found for page', ['page_id' => $pageId]);
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
                    'file' => 'document',
                    default => 'text',
                };

                $attachments[] = [
                    'type' => $att['type'],
                    'url' => $att['payload']['url'] ?? null,
                    'filename' => $att['payload']['title'] ?? 'attachment',
                ];
            }
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
                'platform' => 'facebook',
                'sender_id' => $senderId,
                'timestamp' => $timestamp,
            ],
        ]);

        // Update conversation
        $conversation->update([
            'last_message_preview' => Str::limit($body ?? '[Anexo]', 100),
            'last_message_at' => now(),
            'unread_count' => $conversation->unread_count + 1,
            'status' => $conversation->status === 'resolved' ? 'open' : $conversation->status,
        ]);

        // Mark as seen
        $credentials = $channel->credentials ?? [];
        if ($pageAccessToken = $credentials['page_access_token'] ?? null) {
            $this->client->markSeen($pageId, $pageAccessToken, $senderId);
        }
    }

    /**
     * Process a postback event (button clicks).
     */
    protected function processPostback(string $pageId, array $event): void
    {
        $senderId = $event['sender']['id'];
        $postback = $event['postback'];

        $channel = Channel::where('type', 'facebook')
            ->where('identifier', $pageId)
            ->where('is_active', true)
            ->first();

        if (!$channel) return;

        $contact = $this->findOrCreateContact($channel, $senderId);
        $conversation = $this->findOrCreateConversation($channel, $contact, $senderId);

        Message::create([
            'conversation_id' => $conversation->id,
            'contact_id' => $contact->id,
            'type' => 'text',
            'body' => $postback['title'] ?? $postback['payload'] ?? '[Postback]',
            'direction' => 'inbound',
            'status' => 'delivered',
            'metadata' => [
                'platform' => 'facebook',
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
     * Process delivery receipt.
     */
    protected function processDelivery(array $event): void
    {
        $mids = $event['delivery']['mids'] ?? [];

        foreach ($mids as $mid) {
            Message::where('external_id', $mid)
                ->where('direction', 'outbound')
                ->where('status', 'sent')
                ->update(['status' => 'delivered']);
        }
    }

    /**
     * Process read receipt.
     */
    protected function processRead(array $event): void
    {
        $watermark = $event['read']['watermark'] ?? 0;
        $senderId = $event['sender']['id'] ?? null;

        if ($senderId && $watermark) {
            // Mark all messages before the watermark as read
            Message::where('direction', 'outbound')
                ->where('status', 'delivered')
                ->whereHas('conversation', function ($q) use ($senderId) {
                    $q->where('channel_conversation_id', $senderId);
                })
                ->where('created_at', '<=', \Carbon\Carbon::createFromTimestampMs($watermark))
                ->update(['status' => 'read']);
        }
    }

    /**
     * Send a text message via Messenger.
     */
    public function sendTextMessage(Channel $channel, Conversation $conversation, string $text, ?int $userId = null): ?Message
    {
        $credentials = $channel->credentials ?? [];
        $pageId = $channel->identifier;
        $accessToken = $credentials['page_access_token'] ?? null;
        $recipientId = $conversation->channel_conversation_id;

        if (!$pageId || !$accessToken || !$recipientId) {
            Log::error('Meta Messenger: Missing credentials for sending', [
                'channel_id' => $channel->id,
            ]);
            return null;
        }

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
                'platform' => 'facebook',
                'recipient_id' => $result['recipient_id'] ?? $recipientId,
            ],
        ]);
    }

    /**
     * Find or create a contact from a Messenger sender.
     */
    protected function findOrCreateContact(Channel $channel, string $senderId): Contact
    {
        // Look for existing contact via conversation
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
            $profile = $this->client->getUserProfile($senderId, $accessToken);
        }

        $name = trim(($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? ''));
        if (empty($name)) {
            $name = 'Visitante Messenger #' . substr($senderId, -6);
        }

        return Contact::create([
            'tenant_id' => $channel->tenant_id,
            'name' => $name,
            'avatar' => $profile['profile_pic'] ?? null,
            'source' => 'facebook_messenger',
            'custom_fields' => ['facebook_psid' => $senderId],
        ]);
    }

    /**
     * Find or create a conversation from a Messenger sender.
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
