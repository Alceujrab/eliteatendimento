<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Filament\Resources\TicketResource\RelationManagers;
use App\Models\Ticket;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-ticket';
    protected static string | \UnitEnum | null $navigationGroup = 'Atendimento';
    protected static ?string $navigationLabel = 'Tickets';
    protected static ?string $modelLabel = 'Ticket';
    protected static ?string $pluralModelLabel = 'Tickets';
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'subject';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Schemas\Components\Section::make('Informações do Ticket')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('contact_id')
                        ->label('Contato')
                        ->relationship('contact', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\Select::make('assigned_to')
                        ->label('Responsável')
                        ->relationship('assignedUser', 'name')
                        ->searchable()
                        ->preload(),
                    Forms\Components\TextInput::make('subject')
                        ->label('Assunto')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Forms\Components\RichEditor::make('description')
                        ->label('Descrição')
                        ->columnSpanFull(),
                    Forms\Components\Select::make('category')
                        ->label('Categoria')
                        ->options([
                            'sales' => 'Vendas',
                            'after_sales' => 'Pós-venda',
                            'finance' => 'Financeiro',
                            'documentation' => 'Documentação',
                            'warranty' => 'Garantia',
                            'complaint' => 'Reclamação',
                            'other' => 'Outro',
                        ])
                        ->required(),
                    Forms\Components\Select::make('priority')
                        ->label('Prioridade')
                        ->options([
                            'low' => 'Baixa',
                            'medium' => 'Média',
                            'high' => 'Alta',
                            'urgent' => 'Urgente',
                        ])
                        ->default('medium')
                        ->required()
                        ->native(false),
                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            'open' => 'Aberto',
                            'in_progress' => 'Em Andamento',
                            'waiting_customer' => 'Aguardando Cliente',
                            'waiting_internal' => 'Aguardando Interno',
                            'resolved' => 'Resolvido',
                            'closed' => 'Fechado',
                        ])
                        ->default('open')
                        ->required()
                        ->native(false),
                    Forms\Components\DateTimePicker::make('due_at')
                        ->label('Vencimento'),
                    Forms\Components\TagsInput::make('tags')
                        ->label('Tags')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label('#')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject')
                    ->label('Assunto')
                    ->searchable()
                    ->limit(40)
                    ->sortable(),
                Tables\Columns\TextColumn::make('contact.name')
                    ->label('Contato')
                    ->searchable(),
                Tables\Columns\TextColumn::make('assignedUser.name')
                    ->label('Responsável')
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->label('Categoria')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'sales' => 'Vendas',
                        'after_sales' => 'Pós-venda',
                        'finance' => 'Financeiro',
                        'documentation' => 'Documentação',
                        'warranty' => 'Garantia',
                        'complaint' => 'Reclamação',
                        default => 'Outro',
                    }),
                Tables\Columns\TextColumn::make('priority')
                    ->label('Prior.')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'urgent' => 'danger',
                        'high' => 'warning',
                        'medium' => 'info',
                        'low' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'urgent' => 'Urgente',
                        'high' => 'Alta',
                        'medium' => 'Média',
                        'low' => 'Baixa',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'info',
                        'in_progress' => 'warning',
                        'waiting_customer' => 'orange',
                        'waiting_internal' => 'purple',
                        'resolved' => 'success',
                        'closed' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'open' => 'Aberto',
                        'in_progress' => 'Em Andamento',
                        'waiting_customer' => 'Aguard. Cliente',
                        'waiting_internal' => 'Aguard. Interno',
                        'resolved' => 'Resolvido',
                        'closed' => 'Fechado',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('due_at')
                    ->label('Vencimento')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->color(fn ($record) => $record->isOverdue() ? 'danger' : null),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->multiple()
                    ->options([
                        'open' => 'Aberto',
                        'in_progress' => 'Em Andamento',
                        'waiting_customer' => 'Aguard. Cliente',
                        'resolved' => 'Resolvido',
                        'closed' => 'Fechado',
                    ]),
                Tables\Filters\SelectFilter::make('priority')
                    ->label('Prioridade')
                    ->options([
                        'urgent' => 'Urgente',
                        'high' => 'Alta',
                        'medium' => 'Média',
                        'low' => 'Baixa',
                    ]),
                Tables\Filters\SelectFilter::make('category')
                    ->label('Categoria')
                    ->options([
                        'sales' => 'Vendas',
                        'after_sales' => 'Pós-venda',
                        'finance' => 'Financeiro',
                        'warranty' => 'Garantia',
                        'complaint' => 'Reclamação',
                    ]),
                Tables\Filters\SelectFilter::make('assigned_to')
                    ->label('Responsável')
                    ->relationship('assignedUser', 'name'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
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
            RelationManagers\CommentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereIn('status', ['open', 'in_progress'])->count() ?: null;
    }

    public static function getNavigationBadgeColor(): string | array | null
    {
        return 'danger';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['number', 'subject', 'contact.name'];
    }
}
