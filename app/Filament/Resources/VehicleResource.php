<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleResource\Pages;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-truck';
    protected static string | \UnitEnum | null $navigationGroup = 'Catálogo';
    protected static ?string $navigationLabel = 'Veículos';
    protected static ?string $modelLabel = 'Veículo';
    protected static ?string $pluralModelLabel = 'Veículos';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'full_name';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Schemas\Components\Section::make('Identificação')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('brand')
                        ->label('Marca')
                        ->required()
                        ->maxLength(100),
                    Forms\Components\TextInput::make('model')
                        ->label('Modelo')
                        ->required()
                        ->maxLength(100),
                    Forms\Components\TextInput::make('version')
                        ->label('Versão')
                        ->maxLength(100),
                    Forms\Components\TextInput::make('year_manufacture')
                        ->label('Ano Fab.')
                        ->numeric()
                        ->minValue(1900)
                        ->maxValue(date('Y') + 1),
                    Forms\Components\TextInput::make('year_model')
                        ->label('Ano Mod.')
                        ->numeric()
                        ->minValue(1900)
                        ->maxValue(date('Y') + 2),
                    Forms\Components\TextInput::make('color')
                        ->label('Cor')
                        ->maxLength(50),
                    Forms\Components\Select::make('fuel_type')
                        ->label('Combustível')
                        ->options([
                            'flex' => 'Flex',
                            'gasoline' => 'Gasolina',
                            'ethanol' => 'Etanol',
                            'diesel' => 'Diesel',
                            'electric' => 'Elétrico',
                            'hybrid' => 'Híbrido',
                        ]),
                    Forms\Components\Select::make('transmission')
                        ->label('Câmbio')
                        ->options([
                            'manual' => 'Manual',
                            'automatic' => 'Automático',
                            'cvt' => 'CVT',
                            'automated' => 'Automatizado',
                        ]),
                    Forms\Components\TextInput::make('mileage')
                        ->label('Quilometragem')
                        ->numeric()
                        ->suffix('km'),
                ]),
            Schemas\Components\Section::make('Valores')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('price')
                        ->label('Preço')
                        ->numeric()
                        ->prefix('R$')
                        ->required(),
                    Forms\Components\TextInput::make('fipe_price')
                        ->label('Preço FIPE')
                        ->numeric()
                        ->prefix('R$'),
                ]),
            Schemas\Components\Section::make('Documentação')
                ->columns(3)
                ->collapsed()
                ->schema([
                    Forms\Components\TextInput::make('plate')
                        ->label('Placa')
                        ->maxLength(10),
                    Forms\Components\TextInput::make('chassis')
                        ->label('Chassi')
                        ->maxLength(17),
                    Forms\Components\TextInput::make('renavam')
                        ->label('Renavam')
                        ->maxLength(11),
                ]),
            Schemas\Components\Section::make('Detalhes')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            'available' => 'Disponível',
                            'reserved' => 'Reservado',
                            'sold' => 'Vendido',
                            'maintenance' => 'Manutenção',
                        ])
                        ->default('available')
                        ->required(),
                    Forms\Components\Select::make('condition')
                        ->label('Condição')
                        ->options([
                            'new' => 'Novo (0km)',
                            'used' => 'Usado',
                        ])
                        ->default('used')
                        ->required(),
                    Forms\Components\RichEditor::make('description')
                        ->label('Descrição')
                        ->columnSpanFull(),
                    Forms\Components\TagsInput::make('features')
                        ->label('Opcionais')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('brand')
                    ->label('Marca')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('model')
                    ->label('Modelo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('year_display')
                    ->label('Ano'),
                Tables\Columns\TextColumn::make('color')
                    ->label('Cor')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('fuel_label')
                    ->label('Comb.'),
                Tables\Columns\TextColumn::make('formatted_price')
                    ->label('Preço')
                    ->sortable(query: fn ($query, $direction) => $query->orderBy('price', $direction)),
                Tables\Columns\TextColumn::make('formatted_mileage')
                    ->label('KM'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'reserved' => 'warning',
                        'sold' => 'danger',
                        'maintenance' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'available' => 'Disponível',
                        'reserved' => 'Reservado',
                        'sold' => 'Vendido',
                        'maintenance' => 'Manutenção',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('condition')
                    ->label('Condição')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'new' ? '0km' : 'Usado'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'available' => 'Disponível',
                        'reserved' => 'Reservado',
                        'sold' => 'Vendido',
                        'maintenance' => 'Manutenção',
                    ]),
                Tables\Filters\SelectFilter::make('condition')
                    ->label('Condição')
                    ->options([
                        'new' => '0km',
                        'used' => 'Usado',
                    ]),
                Tables\Filters\SelectFilter::make('fuel_type')
                    ->label('Combustível')
                    ->options([
                        'flex' => 'Flex',
                        'gasoline' => 'Gasolina',
                        'diesel' => 'Diesel',
                        'electric' => 'Elétrico',
                        'hybrid' => 'Híbrido',
                    ]),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['brand', 'model', 'plate'];
    }
}
