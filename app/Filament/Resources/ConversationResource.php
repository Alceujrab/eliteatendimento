<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConversationResource\Pages;
use App\Models\Conversation;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;

class ConversationResource extends Resource
{
    protected static ?string $model = Conversation::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static string | \UnitEnum | null $navigationGroup = 'Atendimento';
    protected static ?string $navigationLabel = 'Inbox';
    protected static ?string $modelLabel = 'Conversa';
    protected static ?string $pluralModelLabel = 'Conversas';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Schemas\Components\Section::make('Conversa')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('contact_id')
                        ->label('Contato')
                        ->relationship('contact', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\Select::make('channel_id')
                        ->label('Canal')
                        ->relationship('channel', 'name')
                        ->required(),
                    Forms\Components\Select::make('assigned_to')
                        ->label('Atribuído a')
                        ->relationship('assignedUser', 'name')
                        ->searchable()
                        ->preload(),
                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            'new' => 'Nova',
                            'open' => 'Aberta',
                            'pending' => 'Pendente',
                            'resolved' => 'Resolvida',
                            'archived' => 'Arquivada',
                        ])
                        ->default('new')
                        ->required()
                        ->native(false),
                    Forms\Components\Select::make('priority')
                        ->label('Prioridade')
                        ->options([
                            'low' => 'Baixa',
                            'medium' => 'Média',
                            'high' => 'Alta',
                            'urgent' => 'Urgente',
                        ])
                        ->default('medium')
                        ->native(false),
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
                Tables\Columns\TextColumn::make('channel.name')
                    ->label('Canal')
                    ->badge()
                    ->color(fn ($record): string => match ($record->channel?->type) {
                        'whatsapp_meta', 'whatsapp_evolution' => 'success',
                        'facebook' => 'info',
                        'instagram' => 'warning',
                        'telegram' => 'primary',
                        'email' => 'gray',
                        'webchat' => 'purple',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('assignedUser.name')
                    ->label('Atendente')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'info',
                        'open' => 'success',
                        'pending' => 'warning',
                        'resolved' => 'gray',
                        'archived' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'new' => 'Nova',
                        'open' => 'Aberta',
                        'pending' => 'Pendente',
                        'resolved' => 'Resolvida',
                        'archived' => 'Arquivada',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('last_message_preview')
                    ->label('Última Mensagem')
                    ->limit(40)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('unread_count')
                    ->label('Não Lidas')
                    ->badge()
                    ->color('danger')
                    ->visible(fn ($state) => $state > 0),
                Tables\Columns\TextColumn::make('last_message_at')
                    ->label('Última Msg')
                    ->dateTime('d/m H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->multiple()
                    ->options([
                        'new' => 'Nova',
                        'open' => 'Aberta',
                        'pending' => 'Pendente',
                        'resolved' => 'Resolvida',
                        'archived' => 'Arquivada',
                    ])
                    ->default(['new', 'open', 'pending']),
                Tables\Filters\SelectFilter::make('channel_id')
                    ->label('Canal')
                    ->relationship('channel', 'name'),
                Tables\Filters\SelectFilter::make('assigned_to')
                    ->label('Atendente')
                    ->relationship('assignedUser', 'name'),
                Tables\Filters\Filter::make('unassigned')
                    ->label('Sem atendente')
                    ->query(fn ($query) => $query->whereNull('assigned_to'))
                    ->toggle(),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Actions\Action::make('assign_me')
                    ->label('Assumir')
                    ->icon('heroicon-o-hand-raised')
                    ->color('success')
                    ->visible(fn (Conversation $record): bool => $record->assigned_to === null)
                    ->action(fn (Conversation $record) => $record->update(['assigned_to' => auth()->id(), 'status' => 'open'])),
                Actions\Action::make('resolve')
                    ->label('Resolver')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Conversation $record): bool => in_array($record->status, ['new', 'open', 'pending']))
                    ->requiresConfirmation()
                    ->action(fn (Conversation $record) => $record->update(['status' => 'resolved', 'resolved_at' => now()])),
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('last_message_at', 'desc')
            ->poll('15s');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConversations::route('/'),
            'edit' => Pages\EditConversation::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereIn('status', ['new', 'open'])->count() ?: null;
    }

    public static function getNavigationBadgeColor(): string | array | null
    {
        return 'success';
    }
}
