<?php
namespace App\Filament\Resources\QuickReplyResource\Pages;
use App\Filament\Resources\QuickReplyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListQuickReplies extends ListRecords
{
    protected static string $resource = QuickReplyResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()->label('Nova Resposta')]; }
}
