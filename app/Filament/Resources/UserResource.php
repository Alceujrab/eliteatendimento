<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-group';
    protected static string | \UnitEnum | null $navigationGroup = 'Configurações';
    protected static ?string $navigationLabel = 'Usuários';
    protected static ?string $modelLabel = 'Usuário';
    protected static ?string $pluralModelLabel = 'Usuários';
    protected static ?int $navigationSort = 5;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Schemas\Components\Section::make('Dados do Usuário')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nome')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('email')
                        ->label('E-mail')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    Forms\Components\TextInput::make('password')
                        ->label('Senha')
                        ->password()
                        ->revealable()
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->maxLength(255),
                    Forms\Components\Select::make('role')
                        ->label('Perfil')
                        ->options([
                            'admin' => 'Administrador',
                            'gestor' => 'Gestor',
                            'vendedor' => 'Vendedor',
                            'atendente' => 'Atendente',
                        ])
                        ->required()
                        ->native(false),
                    Forms\Components\TextInput::make('phone')
                        ->label('Telefone')
                        ->tel()
                        ->maxLength(20),
                    Forms\Components\TextInput::make('max_concurrent_chats')
                        ->label('Máx. Chats Simultâneos')
                        ->numeric()
                        ->default(5)
                        ->minValue(1)
                        ->maxValue(50),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Ativo')
                        ->default(true),
                ]),
            Schemas\Components\Section::make('Disponibilidade da Agenda')
                ->description('Define os horários em que este usuário pode receber agendamentos.')
                ->columns(3)
                ->collapsible()
                ->schema([
                    Forms\Components\Toggle::make('working_hours.mon.enabled')
                        ->label('Segunda ativa')
                        ->default(true),
                    Forms\Components\TimePicker::make('working_hours.mon.start')
                        ->label('Segunda início')
                        ->seconds(false)
                        ->default('08:00'),
                    Forms\Components\TimePicker::make('working_hours.mon.end')
                        ->label('Segunda fim')
                        ->seconds(false)
                        ->default('18:00'),

                    Forms\Components\Toggle::make('working_hours.tue.enabled')
                        ->label('Terça ativa')
                        ->default(true),
                    Forms\Components\TimePicker::make('working_hours.tue.start')
                        ->label('Terça início')
                        ->seconds(false)
                        ->default('08:00'),
                    Forms\Components\TimePicker::make('working_hours.tue.end')
                        ->label('Terça fim')
                        ->seconds(false)
                        ->default('18:00'),

                    Forms\Components\Toggle::make('working_hours.wed.enabled')
                        ->label('Quarta ativa')
                        ->default(true),
                    Forms\Components\TimePicker::make('working_hours.wed.start')
                        ->label('Quarta início')
                        ->seconds(false)
                        ->default('08:00'),
                    Forms\Components\TimePicker::make('working_hours.wed.end')
                        ->label('Quarta fim')
                        ->seconds(false)
                        ->default('18:00'),

                    Forms\Components\Toggle::make('working_hours.thu.enabled')
                        ->label('Quinta ativa')
                        ->default(true),
                    Forms\Components\TimePicker::make('working_hours.thu.start')
                        ->label('Quinta início')
                        ->seconds(false)
                        ->default('08:00'),
                    Forms\Components\TimePicker::make('working_hours.thu.end')
                        ->label('Quinta fim')
                        ->seconds(false)
                        ->default('18:00'),

                    Forms\Components\Toggle::make('working_hours.fri.enabled')
                        ->label('Sexta ativa')
                        ->default(true),
                    Forms\Components\TimePicker::make('working_hours.fri.start')
                        ->label('Sexta início')
                        ->seconds(false)
                        ->default('08:00'),
                    Forms\Components\TimePicker::make('working_hours.fri.end')
                        ->label('Sexta fim')
                        ->seconds(false)
                        ->default('18:00'),

                    Forms\Components\Toggle::make('working_hours.sat.enabled')
                        ->label('Sábado ativo')
                        ->default(true),
                    Forms\Components\TimePicker::make('working_hours.sat.start')
                        ->label('Sábado início')
                        ->seconds(false)
                        ->default('08:00'),
                    Forms\Components\TimePicker::make('working_hours.sat.end')
                        ->label('Sábado fim')
                        ->seconds(false)
                        ->default('12:00'),

                    Forms\Components\Toggle::make('working_hours.sun.enabled')
                        ->label('Domingo ativo')
                        ->default(false),
                    Forms\Components\TimePicker::make('working_hours.sun.start')
                        ->label('Domingo início')
                        ->seconds(false)
                        ->default('08:00'),
                    Forms\Components\TimePicker::make('working_hours.sun.end')
                        ->label('Domingo fim')
                        ->seconds(false)
                        ->default('12:00'),
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
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->label('Perfil')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'gestor' => 'warning',
                        'vendedor' => 'success',
                        'atendente' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin' => 'Admin',
                        'gestor' => 'Gestor',
                        'vendedor' => 'Vendedor',
                        'atendente' => 'Atendente',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefone')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_online')
                    ->label('Online')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('gray'),
                Tables\Columns\TextColumn::make('last_seen_at')
                    ->label('Último acesso')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Perfil')
                    ->options([
                        'admin' => 'Admin',
                        'gestor' => 'Gestor',
                        'vendedor' => 'Vendedor',
                        'atendente' => 'Atendente',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Ativo'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
