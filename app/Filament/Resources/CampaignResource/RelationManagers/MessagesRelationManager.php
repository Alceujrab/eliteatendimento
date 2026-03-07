<?php

namespace App\Filament\Resources\CampaignResource\RelationManagers;

use Filament\Actions;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class MessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';

    protected static ?string $title = 'Envios da Campanha';

    protected static ?string $modelLabel = 'Envio';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('contact.name')
                    ->label('Contato')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact.phone')
                    ->label('Telefone')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'sent' => 'info',
                        'delivered' => 'success',
                        'read' => 'success',
                        'replied' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('error_message')
                    ->label('Erro')
                    ->limit(40)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('sent_at')
                    ->label('Enviado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pendente',
                        'sent' => 'Enviado',
                        'delivered' => 'Entregue',
                        'read' => 'Lido',
                        'replied' => 'Respondido',
                        'failed' => 'Falhou',
                    ]),
            ])
            ->headerActions([])
            ->actions([
                Actions\Action::make('retry')
                    ->label('Reagendar envio')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn ($record): bool => $record->status === 'failed')
                    ->action(fn ($record) => $record->update([
                        'status' => 'pending',
                        'error_message' => null,
                    ])),
            ])
            ->bulkActions([
                Actions\BulkAction::make('retryFailed')
                    ->label('Reagendar falhas')
                    ->icon('heroicon-o-arrow-path')
                    ->action(fn ($records) => $records->each(function ($record) {
                        if ($record->status === 'failed') {
                            $record->update([
                                'status' => 'pending',
                                'error_message' => null,
                            ]);
                        }
                    })),
            ]);
    }
}
