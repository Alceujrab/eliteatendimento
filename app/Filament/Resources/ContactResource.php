<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactResource\Pages;
use App\Models\Contact;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;

class ContactResource extends Resource
{
    protected static ?string $model = Contact::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';
    protected static string | \UnitEnum | null $navigationGroup = 'Catálogo';
    protected static ?string $navigationLabel = 'Contatos';
    protected static ?string $modelLabel = 'Contato';
    protected static ?string $pluralModelLabel = 'Contatos';
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Schemas\Components\Section::make('Informações Pessoais')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nome')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('email')
                        ->label('E-mail')
                        ->email()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('phone')
                        ->label('Telefone')
                        ->tel()
                        ->maxLength(20),
                    Forms\Components\TextInput::make('cpf')
                        ->label('CPF')
                        ->maxLength(14),
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
                    Forms\Components\TagsInput::make('tags')
                        ->label('Tags'),
                ]),
            Schemas\Components\Section::make('Endereço')
                ->columns(3)
                ->collapsed()
                ->schema([
                    Forms\Components\TextInput::make('address')
                        ->label('Endereço')
                        ->columnSpan(3),
                    Forms\Components\TextInput::make('city')
                        ->label('Cidade'),
                    Forms\Components\TextInput::make('state')
                        ->label('Estado')
                        ->maxLength(2),
                ]),
            Schemas\Components\Section::make('Observações')
                ->collapsed()
                ->schema([
                    Forms\Components\Textarea::make('notes')
                        ->label('Notas')
                        ->rows(3),
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
                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->icon('heroicon-m-envelope'),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefone')
                    ->searchable()
                    ->icon('heroicon-m-phone'),
                Tables\Columns\TextColumn::make('source')
                    ->label('Origem')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'whatsapp' => 'success',
                        'facebook' => 'info',
                        'instagram' => 'warning',
                        'website' => 'primary',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('conversations_count')
                    ->counts('conversations')
                    ->label('Conversas'),
                Tables\Columns\TextColumn::make('leads_count')
                    ->counts('leads')
                    ->label('Leads'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Cadastro')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('source')
                    ->label('Origem')
                    ->options([
                        'whatsapp' => 'WhatsApp',
                        'facebook' => 'Facebook',
                        'instagram' => 'Instagram',
                        'website' => 'Website',
                        'referral' => 'Indicação',
                        'walk_in' => 'Presencial',
                        'phone' => 'Telefone',
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
            'index' => Pages\ListContacts::route('/'),
            'create' => Pages\CreateContact::route('/create'),
            'edit' => Pages\EditContact::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'phone', 'cpf'];
    }
}
