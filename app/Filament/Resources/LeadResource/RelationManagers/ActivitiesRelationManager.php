<?php

namespace App\Filament\Resources\LeadResource\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;

class ActivitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';
    protected static ?string $title = 'Atividades';
    protected static ?string $modelLabel = 'Atividade';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\Select::make('type')
                ->label('Tipo')
                ->options([
                    'note' => 'Nota',
                    'call' => 'Ligação',
                    'email' => 'E-mail',
                    'whatsapp' => 'WhatsApp',
                    'meeting' => 'Reunião',
                    'follow_up' => 'Follow-up',
                    'stage_change' => 'Mudança de Etapa',
                ])
                ->required(),
            Forms\Components\Textarea::make('description')
                ->label('Descrição')
                ->required()
                ->rows(3),
            Forms\Components\DateTimePicker::make('scheduled_at')
                ->label('Agendado para'),
            Forms\Components\Toggle::make('is_completed')
                ->label('Concluída'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'note' => 'Nota',
                        'call' => 'Ligação',
                        'email' => 'E-mail',
                        'whatsapp' => 'WhatsApp',
                        'meeting' => 'Reunião',
                        'follow_up' => 'Follow-up',
                        'stage_change' => 'Mudança',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('description')
                    ->label('Descrição')
                    ->limit(50),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuário'),
                Tables\Columns\IconColumn::make('is_completed')
                    ->label('Feito')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
            ->headerActions([
                Actions\CreateAction::make()->label('Nova Atividade')
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
