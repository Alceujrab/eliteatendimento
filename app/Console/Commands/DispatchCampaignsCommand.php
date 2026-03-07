<?php

namespace App\Console\Commands;

use App\Services\WhatsAppCampaignService;
use Illuminate\Console\Command;

class DispatchCampaignsCommand extends Command
{
    protected $signature = 'campaigns:dispatch {--campaign= : ID de campanha específica} {--batch=40 : Quantidade por lote}';

    protected $description = 'Processa envios de campanhas WhatsApp em execução';

    public function handle(WhatsAppCampaignService $service): int
    {
        $campaignId = $this->option('campaign');
        $batch = max(1, (int) $this->option('batch'));

        $result = $service->processRunningCampaigns(
            campaignId: $campaignId ? (int) $campaignId : null,
            batchSize: $batch,
        );

        $this->info('Campanhas processadas: ' . $result['processed_campaigns']);
        $this->line('Enviadas: ' . $result['sent']);
        $this->line('Falhas: ' . $result['failed']);
        $this->line('Concluídas: ' . $result['completed']);

        return self::SUCCESS;
    }
}
