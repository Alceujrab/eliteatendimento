<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\CampaignMessage;
use App\Models\Channel;
use App\Models\Contact;
use App\Models\Conversation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class WhatsAppCampaignService
{
    public function startCampaign(Campaign $campaign): array
    {
        if ($campaign->type !== 'whatsapp') {
            return ['ok' => false, 'message' => 'Somente campanhas WhatsApp podem ser iniciadas por este fluxo.'];
        }

        if (! in_array($campaign->status, ['draft', 'scheduled', 'paused'], true)) {
            return ['ok' => false, 'message' => 'Campanha não está em estado válido para iniciar.'];
        }

        $recipientIds = Contact::query()
            ->where('tenant_id', $campaign->tenant_id)
            ->whereNotNull('phone')
            ->pluck('id');

        DB::transaction(function () use ($campaign, $recipientIds): void {
            foreach ($recipientIds as $contactId) {
                CampaignMessage::query()->firstOrCreate(
                    [
                        'campaign_id' => $campaign->id,
                        'contact_id' => $contactId,
                    ],
                    [
                        'status' => 'pending',
                    ],
                );
            }

            $campaign->update([
                'status' => 'running',
                'started_at' => $campaign->started_at ?? Carbon::now(),
                'total_recipients' => $recipientIds->count(),
                'completed_at' => null,
            ]);
        });

        return ['ok' => true, 'message' => 'Campanha iniciada e fila de contatos preparada.'];
    }

    public function pauseCampaign(Campaign $campaign): void
    {
        if ($campaign->status === 'running') {
            $campaign->update(['status' => 'paused']);
        }
    }

    public function resumeCampaign(Campaign $campaign): void
    {
        if (in_array($campaign->status, ['paused', 'scheduled'], true)) {
            $campaign->update(['status' => 'running']);
        }
    }

    public function cancelCampaign(Campaign $campaign): void
    {
        if (! in_array($campaign->status, ['completed', 'cancelled'], true)) {
            $campaign->update([
                'status' => 'cancelled',
                'completed_at' => Carbon::now(),
            ]);
        }
    }

    public function processRunningCampaigns(?int $campaignId = null, int $batchSize = 40): array
    {
        $query = Campaign::query()
            ->where('type', 'whatsapp')
            ->where('status', 'running');

        if ($campaignId) {
            $query->whereKey($campaignId);
        }

        $campaigns = $query->get();

        $result = [
            'processed_campaigns' => 0,
            'sent' => 0,
            'failed' => 0,
            'completed' => 0,
        ];

        foreach ($campaigns as $campaign) {
            $channel = Channel::query()
                ->where('tenant_id', $campaign->tenant_id)
                ->whereIn('type', ['whatsapp_evolution', 'whatsapp_meta'])
                ->where('is_active', true)
                ->first();

            if (! $channel) {
                continue;
            }

            $pending = CampaignMessage::query()
                ->with('contact')
                ->where('campaign_id', $campaign->id)
                ->where('status', 'pending')
                ->limit($batchSize)
                ->get();

            if ($pending->isEmpty()) {
                if ($this->finalizeIfFinished($campaign)) {
                    $result['completed']++;
                }
                continue;
            }

            $result['processed_campaigns']++;

            foreach ($pending as $item) {
                $contact = $item->contact;

                if (! $contact || blank($contact->phone)) {
                    $item->update([
                        'status' => 'failed',
                        'error_message' => 'Contato sem telefone válido.',
                    ]);
                    $campaign->increment('failed_count');
                    $result['failed']++;
                    continue;
                }

                $conversation = Conversation::query()->firstOrCreate(
                    [
                        'tenant_id' => $campaign->tenant_id,
                        'contact_id' => $contact->id,
                        'channel_id' => $channel->id,
                    ],
                    [
                        'status' => 'open',
                        'channel_conversation_id' => $this->sanitizePhone($contact->phone),
                        'priority' => 'normal',
                        'last_message_preview' => '',
                        'last_message_at' => Carbon::now(),
                        'unread_count' => 0,
                    ],
                );

                if (blank($conversation->channel_conversation_id)) {
                    $conversation->update([
                        'channel_conversation_id' => $this->sanitizePhone($contact->phone),
                    ]);
                }

                $messageText = $this->renderTemplate($campaign->message_template ?? '', $contact->name);

                $sentMessage = app(ChannelDispatcher::class)->sendText($conversation, $messageText, null);

                if ($sentMessage) {
                    $item->update([
                        'status' => 'sent',
                        'external_id' => $sentMessage->external_id,
                        'sent_at' => Carbon::now(),
                    ]);

                    $campaign->increment('sent_count');
                    $campaign->increment('delivered_count');
                    $result['sent']++;
                } else {
                    $item->update([
                        'status' => 'failed',
                        'error_message' => 'Falha no envio pelo canal WhatsApp.',
                    ]);
                    $campaign->increment('failed_count');
                    $result['failed']++;
                }
            }

            if ($this->finalizeIfFinished($campaign->fresh())) {
                $result['completed']++;
            }
        }

        return $result;
    }

    private function finalizeIfFinished(Campaign $campaign): bool
    {
        $pendingCount = CampaignMessage::query()
            ->where('campaign_id', $campaign->id)
            ->where('status', 'pending')
            ->count();

        if ($pendingCount === 0 && $campaign->status === 'running') {
            $campaign->update([
                'status' => 'completed',
                'completed_at' => Carbon::now(),
            ]);

            return true;
        }

        return false;
    }

    private function renderTemplate(string $template, string $name): string
    {
        $firstName = trim(explode(' ', trim($name))[0] ?? '');

        return str_replace(
            ['{{nome}}', '{{first_name}}'],
            [$name, $firstName !== '' ? $firstName : $name],
            $template,
        );
    }

    private function sanitizePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?? $phone;
    }
}
