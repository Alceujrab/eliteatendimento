<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\VehicleFeedSyncService;
use Illuminate\Console\Command;

class SyncVehicleFeedCommand extends Command
{
    protected $signature = 'vehicles:sync-feed {--tenant= : ID do tenant para sincronizar somente uma empresa}';

    protected $description = 'Sincroniza estoque de veículos via feed XML externo';

    public function handle(VehicleFeedSyncService $service): int
    {
        $tenantId = $this->option('tenant');

        if ($tenantId) {
            $tenant = Tenant::query()->find($tenantId);

            if (! $tenant) {
                $this->error('Tenant não encontrado.');
                return self::FAILURE;
            }

            $result = $service->syncTenant($tenant);

            $this->line("Tenant {$tenant->id} - {$tenant->name}");
            $this->line($result['message']);
            $this->line("Criados: {$result['created']} | Atualizados: {$result['updated']} | Removidos: {$result['deleted']} | Ignorados: {$result['skipped']}");

            return $result['status'] === 'error' ? self::FAILURE : self::SUCCESS;
        }

        $results = $service->syncAllTenants();

        foreach ($results as $result) {
            $this->line("Tenant {$result['tenant_id']} - {$result['status']} - {$result['message']}");
            $this->line("Criados: {$result['created']} | Atualizados: {$result['updated']} | Removidos: {$result['deleted']} | Ignorados: {$result['skipped']}");
            $this->newLine();
        }

        return collect($results)->contains(fn (array $result) => $result['status'] === 'error')
            ? self::FAILURE
            : self::SUCCESS;
    }
}
