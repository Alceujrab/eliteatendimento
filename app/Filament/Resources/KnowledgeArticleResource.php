<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KnowledgeArticleResource\Pages;
use App\Models\KnowledgeArticle;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class KnowledgeArticleResource extends Resource
{
    protected static ?string $model = KnowledgeArticle::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-book-open';
    protected static string | \UnitEnum | null $navigationGroup = 'Base de Conhecimento';
    protected static ?string $navigationLabel = 'Artigos';
    protected static ?string $modelLabel = 'Artigo';
    protected static ?string $pluralModelLabel = 'Artigos';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Schemas\Components\Section::make('Artigo')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Título')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Schemas\Components\Utilities\Set $set, ?string $state) => $set('slug', Str::slug($state))),
                    Forms\Components\TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('category')
                        ->label('Categoria')
                        ->options([
                            'general' => 'Geral',
                            'sales' => 'Vendas',
                            'after_sales' => 'Pós-venda',
                            'finance' => 'Financeiro',
                            'vehicles' => 'Veículos',
                            'processes' => 'Processos',
                        ])
                        ->required(),
                    Forms\Components\TagsInput::make('tags')
                        ->label('Tags'),
                    Forms\Components\RichEditor::make('body')
                        ->label('Conteúdo')
                        ->required()
                        ->columnSpanFull(),
                    Forms\Components\Toggle::make('is_published')
                        ->label('Publicado')
                        ->default(true),
                    Forms\Components\Toggle::make('is_internal')
                        ->label('Apenas interno'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->label('Categoria')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'general' => 'Geral',
                        'sales' => 'Vendas',
                        'after_sales' => 'Pós-venda',
                        'finance' => 'Financeiro',
                        'vehicles' => 'Veículos',
                        'processes' => 'Processos',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('author.name')
                    ->label('Autor'),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Publicado')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_internal')
                    ->label('Interno')
                    ->boolean(),
                Tables\Columns\TextColumn::make('views_count')
                    ->label('Views')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('helpful_count')
                    ->label('Útil')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Categoria')
                    ->options([
                        'general' => 'Geral',
                        'sales' => 'Vendas',
                        'after_sales' => 'Pós-venda',
                        'finance' => 'Financeiro',
                        'vehicles' => 'Veículos',
                    ]),
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Publicado'),
                Tables\Filters\TernaryFilter::make('is_internal')
                    ->label('Interno'),
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
            ->defaultSort('updated_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKnowledgeArticles::route('/'),
            'create' => Pages\CreateKnowledgeArticle::route('/create'),
            'edit' => Pages\EditKnowledgeArticle::route('/{record}/edit'),
        ];
    }
}
