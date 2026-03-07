<?php
namespace App\Filament\Resources\VehicleResource\Pages;
use App\Filament\Resources\VehicleResource;
use App\Services\VehicleFeedSyncService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListVehicles extends ListRecords
{
    protected static string $resource = VehicleResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('syncFeed')
                ->label('Sincronizar Estoque')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Sincronizar estoque de veículos')
                ->modalDescription('Busca o estoque no feed XML configurado e atualiza o catálogo desta empresa.')
                ->action(function (VehicleFeedSyncService $syncService): void {
                    $tenant = filament()->getTenant();

                    if (! $tenant) {
                        Notification::make()
                            ->danger()
                            ->title('Empresa não identificada')
                            ->body('Não foi possível identificar o tenant atual.')
                            ->send();
                        return;
                    }

                    $result = $syncService->syncTenant($tenant);

                    $notification = Notification::make()
                        ->title('Sincronização finalizada')
                        ->body("Criados: {$result['created']} | Atualizados: {$result['updated']} | Removidos: {$result['deleted']} | Ignorados: {$result['skipped']}");

                    if ($result['status'] === 'ok') {
                        $notification->success();
                    } else {
                        $notification->danger();
                    }

                    $notification->send();
                }),
            Actions\CreateAction::make()->label('Novo Veículo'),
        ];
    }
}
