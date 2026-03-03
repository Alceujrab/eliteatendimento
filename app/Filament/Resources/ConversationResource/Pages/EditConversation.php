<?php
namespace App\Filament\Resources\ConversationResource\Pages;
use App\Filament\Resources\ConversationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditConversation extends EditRecord
{
    protected static string $resource = ConversationResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('resolve')
                ->label('Resolver')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->action(fn () => $this->record->update(['status' => 'resolved', 'resolved_at' => now()])),
            Actions\DeleteAction::make(),
        ];
    }
}
