<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeadResource\Pages;
use App\Filament\Resources\LeadResource\RelationManagers;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\User;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-fire';
    protected static string | \UnitEnum | null $navigationGroup = 'Vendas';
    protected static ?string $navigationLabel = 'Leads';
    protected static ?string $modelLabel = 'Lead';
    protected static ?string $pluralModelLabel = 'Leads';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Schemas\Components\Section::make('Informações do Lead')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('contact_id')
                        ->label('Contato')
                        ->relationship('contact', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')->label('Nome')->required(),
                            Forms\Components\TextInput::make('phone')->label('Telefone'),
                            Forms\Components\TextInput::make('email')->label('E-mail')->email(),
                        ]),
                    Forms\Components\Select::make('assigned_to')
                        ->label('Responsável')
                        ->relationship('assignedUser', 'name')
                        ->searchable()
                        ->preload(),
                    Forms\Components\Select::make('stage')
                        ->label('Etapa')
                        ->options([
                            'new' => 'Novo',
                            'qualified' => 'Qualificado',
                            'proposal' => 'Proposta',
                            'negotiation' => 'Negociação',
                            'won' => 'Ganho',
                            'lost' => 'Perdido',
                        ])
                        ->default('new')
                        ->required()
                        ->native(false),
                    Forms\Components\Select::make('temperature')
                        ->label('Temperatura')
                        ->options([
                            'hot' => '🔥 Quente',
                            'warm' => '🌡️ Morno',
                            'cold' => '❄️ Frio',
                        ])
                        ->default('warm')
                        ->native(false),
                    Forms\Components\TextInput::make('estimated_value')
                        ->label('Valor Estimado')
                        ->numeric()
                        ->prefix('R$'),
                    Forms\Components\TextInput::make('vehicle_interest')
                        ->label('Veículo de Interesse')
                        ->maxLength(255),
                    Forms\Components\Select::make('source')
                        ->label('Origem')
                        ->options([
                            'whatsapp' => 'WhatsApp',
                            'facebook' => 'Facebook',
                            'instagram' => 'Instagram',
                            'website' => 'Website',
                            'referral' => 'Indicação',
                            'walk_in' => 'Presencial',
                            'phone' => 'Telefone',
                            'other' => 'Outro',
                        ]),
                    Forms\Components\DateTimePicker::make('next_follow_up')
                        ->label('Próximo Follow-up'),
                ]),
            Schemas\Components\Section::make('Detalhes')
                ->collapsed()
                ->schema([
                    Forms\Components\Textarea::make('notes')
                        ->label('Notas')
                        ->rows(3),
                    Forms\Components\Textarea::make('lost_reason')
                        ->label('Motivo da Perda')
                        ->rows(2)
                        ->visible(fn (Schemas\Components\Utilities\Get $get): bool => $get('stage') === 'lost'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('contact.name')
                    ->label('Contato')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('assignedUser.name')
                    ->label('Responsável')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stage')
                    ->label('Etapa')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'info',
                        'qualified' => 'primary',
                        'proposal' => 'warning',
                        'negotiation' => 'orange',
                        'won' => 'success',
                        'lost' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'new' => 'Novo',
                        'qualified' => 'Qualificado',
                        'proposal' => 'Proposta',
                        'negotiation' => 'Negociação',
                        'won' => 'Ganho',
                        'lost' => 'Perdido',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('temperature')
                    ->label('Temp.')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'hot' => 'danger',
                        'warm' => 'warning',
                        'cold' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'hot' => 'Quente',
                        'warm' => 'Morno',
                        'cold' => 'Frio',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('estimated_value')
                    ->label('Valor Est.')
                    ->money('BRL')
                    ->sortable(),
                Tables\Columns\TextColumn::make('vehicle_interest')
                    ->label('Veículo')
                    ->limit(30)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('source')
                    ->label('Origem')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('next_follow_up')
                    ->label('Follow-up')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->color(fn ($record) => $record->next_follow_up && $record->next_follow_up->isPast() ? 'danger' : null),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('stage')
                    ->label('Etapa')
                    ->multiple()
                    ->options([
                        'new' => 'Novo',
                        'qualified' => 'Qualificado',
                        'proposal' => 'Proposta',
                        'negotiation' => 'Negociação',
                        'won' => 'Ganho',
                        'lost' => 'Perdido',
                    ]),
                Tables\Filters\SelectFilter::make('temperature')
                    ->label('Temperatura')
                    ->options([
                        'hot' => 'Quente',
                        'warm' => 'Morno',
                        'cold' => 'Frio',
                    ]),
                Tables\Filters\SelectFilter::make('assigned_to')
                    ->label('Responsável')
                    ->relationship('assignedUser', 'name'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Actions\Action::make('advance')
                    ->label('Avançar')
                    ->icon('heroicon-o-arrow-right')
                    ->color('success')
                    ->visible(fn (Lead $record): bool => in_array($record->stage, Lead::activeStages()))
                    ->action(function (Lead $record) {
                        $stages = Lead::stages();
                        $currentIndex = array_search($record->stage, $stages);
                        if ($currentIndex !== false && $currentIndex < count($stages) - 2) {
                            $record->update(['stage' => $stages[$currentIndex + 1]]);
                        }
                    }),
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ActivitiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeads::route('/'),
            'create' => Pages\CreateLead::route('/create'),
            'edit' => Pages\EditLead::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereIn('stage', Lead::activeStages())->count() ?: null;
    }

    public static function getNavigationBadgeColor(): string | array | null
    {
        return 'warning';
    }
}
