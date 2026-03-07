<?php
namespace App\Filament\Resources\CampaignResource\Pages;
use App\Filament\Resources\CampaignResource;
use App\Services\WhatsAppCampaignService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListCampaigns extends ListRecords
{
    protected static string $resource = CampaignResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('dispatchBatch')
                ->label('Processar envios')
                ->icon('heroicon-o-paper-airplane')
                ->color('gray')
                ->action(function (WhatsAppCampaignService $service): void {
                    $result = $service->processRunningCampaigns(null, 80);

                    Notification::make()
                        ->success()
                        ->title('Lote processado')
                        ->body("Campanhas: {$result['processed_campaigns']} | Enviadas: {$result['sent']} | Falhas: {$result['failed']}")
                        ->send();
                }),
            Actions\CreateAction::make()->label('Nova Campanha'),
        ];
    }
}
