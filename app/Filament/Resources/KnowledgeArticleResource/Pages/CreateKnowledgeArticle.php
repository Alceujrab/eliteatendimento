<?php
namespace App\Filament\Resources\KnowledgeArticleResource\Pages;
use App\Filament\Resources\KnowledgeArticleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateKnowledgeArticle extends CreateRecord
{
    protected static string $resource = KnowledgeArticleResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['author_id'] = auth()->id();
        return $data;
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
