<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignResource\Pages;
use App\Filament\Resources\CampaignResource\RelationManagers\MessagesRelationManager;
use App\Models\Campaign;
use App\Services\WhatsAppCampaignService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;

class CampaignResource extends Resource
{
    protected static ?string $model = Campaign::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-megaphone';
    protected static string | \UnitEnum | null $navigationGroup = 'Marketing';
    protected static ?string $navigationLabel = 'Campanhas';
    protected static ?string $modelLabel = 'Campanha';
    protected static ?string $pluralModelLabel = 'Campanhas';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Schemas\Components\Section::make('Campanha')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nome')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('type')
                        ->label('Tipo')
                        ->options([
                            'whatsapp' => 'WhatsApp',
                            'sms' => 'SMS',
                            'email' => 'E-mail',
                        ])
                        ->required()
                        ->native(false),
                    Forms\Components\Textarea::make('description')
                        ->label('Descrição')
                        ->rows(2)
                        ->columnSpanFull(),
                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            'draft' => 'Rascunho',
                            'scheduled' => 'Agendada',
                            'running' => 'Executando',
                            'paused' => 'Pausada',
                            'completed' => 'Concluída',
                            'cancelled' => 'Cancelada',
                        ])
                        ->default('draft')
                        ->required()
                        ->native(false),
                    Forms\Components\DateTimePicker::make('scheduled_at')
                        ->label('Agendar para'),
                ]),
            Schemas\Components\Section::make('Mensagem')
                ->schema([
                    Forms\Components\Textarea::make('message_template')
                        ->label('Template da Mensagem')
                        ->required()
                        ->rows(5)
                        ->helperText('Use {{nome}} para personalizar com o nome do contato.'),
                ]),
            Schemas\Components\Section::make('Estatísticas')
                ->columns(3)
                ->visible(fn (?Campaign $record): bool => $record !== null)
                ->schema([
                    Forms\Components\Placeholder::make('total_recipients')
                        ->label('Total Destinatários')
                        ->content(fn (?Campaign $record): string => $record?->total_recipients ?? '0'),
                    Forms\Components\Placeholder::make('sent_count')
                        ->label('Enviadas')
                        ->content(fn (?Campaign $record): string => $record?->sent_count ?? '0'),
                    Forms\Components\Placeholder::make('delivered_count')
                        ->label('Entregues')
                        ->content(fn (?Campaign $record): string => $record?->delivered_count ?? '0'),
                    Forms\Components\Placeholder::make('read_count')
                        ->label('Lidas')
                        ->content(fn (?Campaign $record): string => $record?->read_count ?? '0'),
                    Forms\Components\Placeholder::make('replied_count')
                        ->label('Respondidas')
                        ->content(fn (?Campaign $record): string => $record?->replied_count ?? '0'),
                    Forms\Components\Placeholder::make('delivery_rate')
                        ->label('Taxa de Entrega')
                        ->content(fn (?Campaign $record): string => ($record?->delivery_rate ?? 0) . '%'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'whatsapp' => 'WhatsApp',
                        'sms' => 'SMS',
                        'email' => 'E-mail',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'scheduled' => 'info',
                        'running' => 'warning',
                        'paused' => 'orange',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Rascunho',
                        'scheduled' => 'Agendada',
                        'running' => 'Executando',
                        'paused' => 'Pausada',
                        'completed' => 'Concluída',
                        'cancelled' => 'Cancelada',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('total_recipients')
                    ->label('Destinatários')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sent_count')
                    ->label('Enviadas')
                    ->numeric(),
                Tables\Columns\TextColumn::make('delivery_rate')
                    ->label('Entrega')
                    ->suffix('%')
                    ->sortable(query: fn ($query, $direction) => $query->orderByRaw(
                        'CASE WHEN sent_count > 0 THEN (delivered_count * 100.0 / sent_count) ELSE 0 END ' . $direction
                    )),
                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Agendada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criada')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Rascunho',
                        'scheduled' => 'Agendada',
                        'running' => 'Executando',
                        'completed' => 'Concluída',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'whatsapp' => 'WhatsApp',
                        'sms' => 'SMS',
                        'email' => 'E-mail',
                    ]),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Actions\Action::make('startCampaign')
                    ->label('Iniciar')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->visible(fn (Campaign $record): bool => in_array($record->status, ['draft', 'scheduled', 'paused']))
                    ->action(function (Campaign $record, WhatsAppCampaignService $service): void {
                        $result = $service->startCampaign($record);

                        Notification::make()
                            ->title($result['ok'] ? 'Campanha iniciada' : 'Não foi possível iniciar')
                            ->body($result['message'])
                            ->{$result['ok'] ? 'success' : 'danger'}()
                            ->send();
                    }),
                Actions\Action::make('pauseCampaign')
                    ->label('Pausar')
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->visible(fn (Campaign $record): bool => $record->status === 'running')
                    ->action(function (Campaign $record, WhatsAppCampaignService $service): void {
                        $service->pauseCampaign($record);

                        Notification::make()
                            ->success()
                            ->title('Campanha pausada')
                            ->send();
                    }),
                Actions\Action::make('resumeCampaign')
                    ->label('Retomar')
                    ->icon('heroicon-o-play-circle')
                    ->color('info')
                    ->visible(fn (Campaign $record): bool => in_array($record->status, ['paused', 'scheduled']))
                    ->action(function (Campaign $record, WhatsAppCampaignService $service): void {
                        $service->resumeCampaign($record);

                        Notification::make()
                            ->success()
                            ->title('Campanha retomada')
                            ->send();
                    }),
                Actions\Action::make('cancelCampaign')
                    ->label('Cancelar')
                    ->icon('heroicon-o-stop-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Campaign $record): bool => ! in_array($record->status, ['cancelled', 'completed']))
                    ->action(function (Campaign $record, WhatsAppCampaignService $service): void {
                        $service->cancelCampaign($record);

                        Notification::make()
                            ->success()
                            ->title('Campanha cancelada')
                            ->send();
                    }),
                Actions\Action::make('processNow')
                    ->label('Processar agora')
                    ->icon('heroicon-o-bolt')
                    ->color('gray')
                    ->visible(fn (Campaign $record): bool => $record->status === 'running')
                    ->action(function (Campaign $record, WhatsAppCampaignService $service): void {
                        $result = $service->processRunningCampaigns((int) $record->id, 40);

                        Notification::make()
                            ->success()
                            ->title('Processamento executado')
                            ->body("Enviadas: {$result['sent']} | Falhas: {$result['failed']}")
                            ->send();
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
            MessagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCampaigns::route('/'),
            'create' => Pages\CreateCampaign::route('/create'),
            'edit' => Pages\EditCampaign::route('/{record}/edit'),
        ];
    }
}
