<?php
namespace App\Filament\Resources\LeadResource\Pages;
use App\Filament\Pages\CrmBoard;
use App\Filament\Resources\LeadResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLeads extends ListRecords
{
    protected static string $resource = LeadResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('crm_board')
                ->label('Abrir CRM Board')
                ->icon('heroicon-o-rectangle-group')
                ->url(CrmBoard::getUrl()),
            Actions\CreateAction::make()->label('Novo Lead'),
        ];
    }
}
