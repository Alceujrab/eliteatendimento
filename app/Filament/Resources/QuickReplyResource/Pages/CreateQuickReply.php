<?php
namespace App\Filament\Resources\QuickReplyResource\Pages;
use App\Filament\Resources\QuickReplyResource;
use Filament\Resources\Pages\CreateRecord;
class CreateQuickReply extends CreateRecord
{
    protected static string $resource = QuickReplyResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        return $data;
    }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
