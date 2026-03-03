<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SlaPolicyResource\Pages;
use App\Models\SlaPolicy;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;

class SlaPolicyResource extends Resource
{
    protected static ?string $model = SlaPolicy::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clock';
    protected static string | \UnitEnum | null $navigationGroup = 'Configurações';
    protected static ?string $navigationLabel = 'Políticas SLA';
    protected static ?string $modelLabel = 'Política SLA';
    protected static ?string $pluralModelLabel = 'Políticas SLA';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Schemas\Components\Section::make('Política SLA')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nome')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('category')
                        ->label('Categoria')
                        ->options([
                            'sales' => 'Vendas',
                            'after_sales' => 'Pós-venda',
                            'finance' => 'Financeiro',
                            'warranty' => 'Garantia',
                            'complaint' => 'Reclamação',
                            'general' => 'Geral',
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
                        ->required(),
                    Forms\Components\TextInput::make('first_response_minutes')
                        ->label('Primeira Resposta (min)')
                        ->numeric()
                        ->required()
                        ->suffix('min'),
                    Forms\Components\TextInput::make('resolution_minutes')
                        ->label('Resolução (min)')
                        ->numeric()
                        ->required()
                        ->suffix('min'),
                    Forms\Components\Toggle::make('is_default')
                        ->label('Padrão'),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Ativo')
                        ->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category')
                    ->label('Categoria')
                    ->badge(),
                Tables\Columns\TextColumn::make('priority')
                    ->label('Prioridade')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'urgent' => 'danger',
                        'high' => 'warning',
                        'medium' => 'info',
                        'low' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('first_response_minutes')
                    ->label('1ª Resposta')
                    ->suffix(' min')
                    ->sortable(),
                Tables\Columns\TextColumn::make('resolution_minutes')
                    ->label('Resolução')
                    ->suffix(' min')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_default')
                    ->label('Padrão')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean(),
            ])
            ->actions([Actions\EditAction::make()])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSlaPolicies::route('/'),
            'create' => Pages\CreateSlaPolicy::route('/create'),
            'edit' => Pages\EditSlaPolicy::route('/{record}/edit'),
        ];
    }
}
