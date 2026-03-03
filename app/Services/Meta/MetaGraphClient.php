<?php

declare(strict_types=1);

namespace App\Services\Meta;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * HTTP client wrapper for Meta Graph API.
 * Used by both Facebook Messenger and Instagram DM services.
 */
class MetaGraphClient
{
    protected string $baseUrl;
    protected string $version;
    protected int $timeout;
    protected array $retry;

    public function __construct()
    {
        $this->baseUrl = config('meta.graph_api_url', 'https://graph.facebook.com');
        $this->version = config('meta.graph_api_version', 'v21.0');
        $this->timeout = config('meta.timeout', 30);
        $this->retry = config('meta.retry', ['times' => 3, 'sleep' => 200]);
    }

    /**
     * Build a configured HTTP client for a specific access token.
     */
    protected function client(string $accessToken): PendingRequest
    {
        return Http::baseUrl("{$this->baseUrl}/{$this->version}")
            ->withToken($accessToken)
            ->timeout($this->timeout)
            ->retry($this->retry['times'], $this->retry['sleep']);
    }

    /**
     * Send a message via the Messenger Send API.
     * Works for both Facebook Messenger and Instagram DMs.
     *
     * @param string $pageId      The Facebook Page ID or Instagram Professional Account ID
     * @param string $accessToken The Page Access Token
     * @param string $recipientId The recipient's PSID (Facebook) or IGSID (Instagram)
     * @param array  $message     The message payload
     */
    public function sendMessage(string $pageId, string $accessToken, string $recipientId, array $message): ?array
    {
        try {
            $response = $this->client($accessToken)
                ->post("{$pageId}/messages", [
                    'recipient' => ['id' => $recipientId],
                    'message' => $message,
                    'messaging_type' => 'RESPONSE',
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Meta API: Failed to send message', [
                'status' => $response->status(),
                'body' => $response->json(),
                'page_id' => $pageId,
                'recipient_id' => $recipientId,
            ]);

            return null;
        } catch (\Throwable $e) {
            Log::error('Meta API: Exception sending message', [
                'error' => $e->getMessage(),
                'page_id' => $pageId,
                'recipient_id' => $recipientId,
            ]);

            return null;
        }
    }

    /**
     * Send a text message.
     */
    public function sendTextMessage(string $pageId, string $accessToken, string $recipientId, string $text): ?array
    {
        return $this->sendMessage($pageId, $accessToken, $recipientId, [
            'text' => $text,
        ]);
    }

    /**
     * Send an image message.
     */
    public function sendImageMessage(string $pageId, string $accessToken, string $recipientId, string $imageUrl): ?array
    {
        return $this->sendMessage($pageId, $accessToken, $recipientId, [
            'attachment' => [
                'type' => 'image',
                'payload' => ['url' => $imageUrl, 'is_reusable' => true],
            ],
        ]);
    }

    /**
     * Send a file/document message.
     */
    public function sendFileMessage(string $pageId, string $accessToken, string $recipientId, string $fileUrl): ?array
    {
        return $this->sendMessage($pageId, $accessToken, $recipientId, [
            'attachment' => [
                'type' => 'file',
                'payload' => ['url' => $fileUrl, 'is_reusable' => true],
            ],
        ]);
    }

    /**
     * Get user profile from Meta (for contact creation).
     */
    public function getUserProfile(string $userId, string $accessToken, array $fields = ['first_name', 'last_name', 'profile_pic']): ?array
    {
        try {
            $response = $this->client($accessToken)
                ->get($userId, [
                    'fields' => implode(',', $fields),
                ]);

            return $response->successful() ? $response->json() : null;
        } catch (\Throwable $e) {
            Log::warning('Meta API: Failed to get user profile', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Subscribe a Facebook Page to webhook events.
     */
    public function subscribePageToWebhooks(string $pageId, string $accessToken): bool
    {
        try {
            $response = $this->client($accessToken)
                ->post("{$pageId}/subscribed_apps", [
                    'subscribed_fields' => 'messages,messaging_postbacks,messaging_optins,message_deliveries,message_reads',
                ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('Meta API: Failed to subscribe page', [
                'page_id' => $pageId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Mark a message as seen (sender action).
     */
    public function markSeen(string $pageId, string $accessToken, string $recipientId): void
    {
        try {
            $this->client($accessToken)
                ->post("{$pageId}/messages", [
                    'recipient' => ['id' => $recipientId],
                    'sender_action' => 'mark_seen',
                ]);
        } catch (\Throwable $e) {
            // Silently fail — not critical
        }
    }

    /**
     * Download an attachment from Meta CDN.
     */
    public function getAttachment(string $attachmentId, string $accessToken): ?string
    {
        try {
            $response = $this->client($accessToken)->get($attachmentId);

            if ($response->successful()) {
                $data = $response->json();
                return $data['url'] ?? null;
            }

            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
