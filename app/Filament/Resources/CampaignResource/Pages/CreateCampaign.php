<?php
namespace App\Filament\Resources\CampaignResource\Pages;
use App\Filament\Resources\CampaignResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateCampaign extends CreateRecord
{
    protected static string $resource = CampaignResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        $data['tenant_id'] = filament()->getTenant()->id;
        return $data;
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
