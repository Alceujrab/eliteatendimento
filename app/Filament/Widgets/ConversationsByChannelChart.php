<?php

namespace App\Filament\Widgets;

use App\Models\Conversation;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ConversationsByChannelChart extends ChartWidget
{
    protected ?string $heading = 'Conversas por Canal';
    protected static ?int $sort = 2;
    protected ?string $maxHeight = '300px';
    protected string | null $pollingInterval = '60s';

    protected function getData(): array
    {
        $tenant = filament()->getTenant();

        $data = Conversation::where('conversations.tenant_id', $tenant->id)
            ->join('channels', 'conversations.channel_id', '=', 'channels.id')
            ->select('channels.type', DB::raw('count(*) as total'))
            ->groupBy('channels.type')
            ->pluck('total', 'type')
            ->toArray();

        $labels = [];
        $values = [];
        $colors = [];

        $channelLabels = [
            'whatsapp' => 'WhatsApp',
            'telegram' => 'Telegram',
            'instagram' => 'Instagram',
            'facebook' => 'Facebook',
            'webchat' => 'WebChat',
            'email' => 'E-mail',
            'sms' => 'SMS',
            'phone' => 'Telefone',
        ];

        $channelColors = [
            'whatsapp' => '#25D366',
            'telegram' => '#0088cc',
            'instagram' => '#E4405F',
            'facebook' => '#1877F2',
            'webchat' => '#6366f1',
            'email' => '#f59e0b',
            'sms' => '#8b5cf6',
            'phone' => '#64748b',
        ];

        foreach ($data as $type => $total) {
            $labels[] = $channelLabels[$type] ?? ucfirst($type);
            $values[] = $total;
            $colors[] = $channelColors[$type] ?? '#94a3b8';
        }

        return [
            'datasets' => [
                [
                    'label' => 'Conversas',
                    'data' => $values,
                    'backgroundColor' => $colors,
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'right',
                ],
            ],
        ];
    }
}
