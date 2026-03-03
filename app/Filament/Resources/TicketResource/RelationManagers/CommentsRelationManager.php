<?php

namespace App\Filament\Resources\TicketResource\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';
    protected static ?string $title = 'Comentários';
    protected static ?string $modelLabel = 'Comentário';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\RichEditor::make('body')
                ->label('Comentário')
                ->required()
                ->columnSpanFull(),
            Forms\Components\Toggle::make('is_internal')
                ->label('Nota interna (não visível ao cliente)'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Autor'),
                Tables\Columns\TextColumn::make('body')
                    ->label('Comentário')
                    ->html()
                    ->limit(80),
                Tables\Columns\IconColumn::make('is_internal')
                    ->label('Interno')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Actions\CreateAction::make()->label('Comentar')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();
                        return $data;
                    }),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ]);
    }
}
