<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuickReplyResource\Pages;
use App\Models\QuickReply;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;

class QuickReplyResource extends Resource
{
    protected static ?string $model = QuickReply::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-bolt';
    protected static string | \UnitEnum | null $navigationGroup = 'Configurações';
    protected static ?string $navigationLabel = 'Respostas Rápidas';
    protected static ?string $modelLabel = 'Resposta Rápida';
    protected static ?string $pluralModelLabel = 'Respostas Rápidas';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Schemas\Components\Section::make('Resposta Rápida')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Título')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('shortcut')
                        ->label('Atalho')
                        ->prefix('/')
                        ->maxLength(50)
                        ->helperText('Ex: /saudacao'),
                    Forms\Components\Select::make('category')
                        ->label('Categoria')
                        ->options([
                            'greeting' => 'Saudação',
                            'sales' => 'Vendas',
                            'support' => 'Suporte',
                            'closing' => 'Encerramento',
                            'general' => 'Geral',
                        ]),
                    Forms\Components\Toggle::make('is_global')
                        ->label('Global (todos os usuários)')
                        ->default(false),
                    Forms\Components\Textarea::make('body')
                        ->label('Mensagem')
                        ->required()
                        ->rows(4)
                        ->columnSpanFull()
                        ->helperText('Use {{nome}} para personalizar.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable(),
                Tables\Columns\TextColumn::make('shortcut')
                    ->label('Atalho')
                    ->prefix('/'),
                Tables\Columns\TextColumn::make('category')
                    ->label('Categoria')
                    ->badge(),
                Tables\Columns\TextColumn::make('body')
                    ->label('Mensagem')
                    ->limit(50),
                Tables\Columns\IconColumn::make('is_global')
                    ->label('Global')
                    ->boolean(),
            ])
            ->actions([Actions\EditAction::make()])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuickReplies::route('/'),
            'create' => Pages\CreateQuickReply::route('/create'),
            'edit' => Pages\EditQuickReply::route('/{record}/edit'),
        ];
    }
}
