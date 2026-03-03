<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Channel;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\Meta\InstagramService;
use App\Services\Meta\MessengerService;
use Illuminate\Support\Facades\Log;

/**
 * Routes outbound messages to the correct channel service
 * based on the conversation's channel type.
 *
 * Supported channel types:
 * - facebook          → MessengerService  (Meta Send API)
 * - instagram         → InstagramService  (Meta Send API via connected Page)
 * - whatsapp_evolution → Evolution API     (via wallacemartinss package)
 * - whatsapp_meta     → Meta Cloud API    (future)
 * - webchat / email   → Local only        (no external dispatch)
 */
class ChannelDispatcher
{
    public function __construct(
        protected MessengerService $messengerService,
        protected InstagramService $instagramService,
    ) {}

    /**
     * Send a text message through the appropriate external channel.
     *
     * Returns the Message model created by the service, or null if
     * the channel type does not support external dispatch (the caller
     * should create a local message in that case).
     */
    public function sendText(
        Conversation $conversation,
        string       $text,
        ?int         $userId = null,
    ): ?Message {
        $channel = $conversation->channel;

        if (! $channel) {
            Log::warning('ChannelDispatcher: Conversation has no channel', [
                'conversation_id' => $conversation->id,
            ]);
            return null;
        }

        return match ($channel->type) {
            'facebook'  => $this->messengerService->sendTextMessage($channel, $conversation, $text, $userId),
            'instagram' => $this->instagramService->sendTextMessage($channel, $conversation, $text, $userId),
            'whatsapp_evolution' => $this->sendViaEvolution($channel, $conversation, $text, $userId),
            default => null, // webchat, email, sms – local only
        };
    }

    /**
     * Send a text message via Evolution API (WhatsApp).
     * Uses the wallacemartinss/filament-whatsapp-conector package HTTP client.
     */
    protected function sendViaEvolution(
        Channel      $channel,
        Conversation $conversation,
        string       $text,
        ?int         $userId = null,
    ): ?Message {
        $credentials = $channel->credentials ?? [];
        $instanceName = $credentials['instance_name'] ?? null;
        $phone = $conversation->channel_conversation_id; // phone number

        if (! $instanceName || ! $phone) {
            Log::error('ChannelDispatcher: Missing Evolution credentials', [
                'channel_id' => $channel->id,
            ]);
            return null;
        }

        // Read from IntegrationSetting (per-tenant) with config fallback
        $evoSettings = \App\Models\IntegrationSetting::forTenant($channel->tenant_id, 'evolution');
        $baseUrl = rtrim($evoSettings?->credential('base_url') ?? config('filament-evolution.api.url', ''), '/');
        $apiKey  = $evoSettings?->credential('api_key') ?? config('filament-evolution.api.api_key');

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'apikey' => $apiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout(config('filament-evolution.api.timeout', 30))
            ->post("{$baseUrl}/message/sendText/{$instanceName}", [
                'number' => $phone,
                'text'   => $text,
            ]);

            if (! $response->successful()) {
                Log::error('ChannelDispatcher: Evolution API error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return null;
            }

            $data = $response->json();

            return Message::create([
                'conversation_id' => $conversation->id,
                'user_id'         => $userId,
                'contact_id'      => $conversation->contact_id,
                'type'            => 'text',
                'body'            => $text,
                'direction'       => 'outbound',
                'status'          => 'sent',
                'external_id'     => $data['key']['id'] ?? null,
                'metadata'        => [
                    'platform'      => 'whatsapp_evolution',
                    'instance'      => $instanceName,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('ChannelDispatcher: Evolution API exception', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Check if the channel type supports external dispatch.
     */
    public function supportsExternalDispatch(string $channelType): bool
    {
        return in_array($channelType, [
            'facebook',
            'instagram',
            'whatsapp_evolution',
            'whatsapp_meta',
        ], true);
    }
}
