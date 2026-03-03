<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhooks;

use App\Services\Meta\InstagramService;
use App\Services\Meta\MessengerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

/**
 * Handles webhooks from Meta Platform (Facebook Messenger + Instagram DMs).
 *
 * Both platforms use the same webhook endpoint.
 * Events are routed to the appropriate service based on the entry object type.
 *
 * Setup in Meta Developer Portal:
 * 1. Create a Meta App with Messenger and Instagram products
 * 2. Set the webhook URL to: {APP_URL}/api/webhooks/meta
 * 3. Set the verify token to match META_VERIFY_TOKEN in .env
 * 4. Subscribe to: messages, messaging_postbacks, messaging_optins, message_deliveries, message_reads
 */
class MetaWebhookController extends Controller
{
    public function __construct(
        protected MessengerService $messengerService,
        protected InstagramService $instagramService,
    ) {}

    /**
     * Webhook verification (GET request from Meta).
     * Meta sends a GET request with hub.mode, hub.verify_token, and hub.challenge.
     * Checks verify_token against ALL active meta integrations in the DB,
     * then falls back to the config value.
     */
    public function verify(Request $request): Response
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode !== 'subscribe' || empty($token)) {
            return response('Forbidden', 403);
        }

        // Check DB-stored verify tokens across all tenants
        $matched = \App\Models\IntegrationSetting::where('provider', 'meta')
            ->where('is_active', true)
            ->get()
            ->contains(fn ($s) => $s->credential('verify_token') === $token);

        // Fallback to config
        if (! $matched) {
            $matched = $token === config('meta.webhook.verify_token');
        }

        if ($matched) {
            Log::info('Meta Webhook: Verification successful');
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        Log::warning('Meta Webhook: Verification failed');
        return response('Forbidden', 403);
    }

    /**
     * Receive webhook events (POST request from Meta).
     */
    public function handle(Request $request): JsonResponse
    {
        // Validate signature if app_secret is configured
        if (!$this->validateSignature($request)) {
            Log::warning('Meta Webhook: Invalid signature');
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        $payload = $request->all();
        $object = $payload['object'] ?? null;

        Log::debug('Meta Webhook: Received event', [
            'object' => $object,
            'entries' => count($payload['entry'] ?? []),
        ]);

        // Must respond 200 quickly to avoid Meta retries
        // Process entries
        foreach ($payload['entry'] ?? [] as $entry) {
            try {
                match ($object) {
                    'page' => $this->messengerService->handleIncomingMessage($entry),
                    'instagram' => $this->instagramService->handleIncomingMessage($entry),
                    default => Log::info('Meta Webhook: Unknown object type', ['object' => $object]),
                };
            } catch (\Throwable $e) {
                Log::error('Meta Webhook: Error processing entry', [
                    'object' => $object,
                    'entry_id' => $entry['id'] ?? 'unknown',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Validate the X-Hub-Signature-256 header.
     * Checks against all active meta integration secrets, then config fallback.
     */
    protected function validateSignature(Request $request): bool
    {
        $signature = $request->header('X-Hub-Signature-256');

        // Collect all possible app_secrets (DB + config fallback)
        $secrets = \App\Models\IntegrationSetting::where('provider', 'meta')
            ->where('is_active', true)
            ->get()
            ->map(fn ($s) => $s->credential('app_secret'))
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $configSecret = config('meta.app_secret');
        if ($configSecret && ! in_array($configSecret, $secrets, true)) {
            $secrets[] = $configSecret;
        }

        // If no secrets configured at all, skip validation (development mode)
        if (empty($secrets)) {
            return true;
        }

        if (empty($signature)) {
            return false;
        }

        $content = $request->getContent();

        foreach ($secrets as $secret) {
            $expected = 'sha256=' . hash_hmac('sha256', $content, $secret);
            if (hash_equals($expected, $signature)) {
                return true;
            }
        }

        return false;
    }
}
