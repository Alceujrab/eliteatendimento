<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AutomationResource\Pages;
use App\Models\Automation;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;

class AutomationResource extends Resource
{
    protected static ?string $model = Automation::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static string | \UnitEnum | null $navigationGroup = 'Configurações';
    protected static ?string $navigationLabel = 'Automações';
    protected static ?string $modelLabel = 'Automação';
    protected static ?string $pluralModelLabel = 'Automações';
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Schemas\Components\Section::make('Automação')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nome')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('trigger_type')
                        ->label('Tipo de Gatilho')
                        ->options([
                            'new_conversation' => 'Nova conversa',
                            'new_lead' => 'Novo lead',
                            'new_ticket' => 'Novo ticket',
                            'message_received' => 'Mensagem recebida',
                            'lead_stage_changed' => 'Mudança de etapa',
                            'ticket_overdue' => 'Ticket vencido',
                        ])
                        ->required()
                        ->native(false),
                    Forms\Components\Textarea::make('description')
                        ->label('Descrição')
                        ->rows(2)
                        ->columnSpanFull(),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Ativo')
                        ->default(true),
                ]),
            Schemas\Components\Section::make('Integração n8n')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('n8n_workflow_id')
                        ->label('Workflow ID (n8n)')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('n8n_webhook_url')
                        ->label('Webhook URL')
                        ->url()
                        ->maxLength(500),
                ]),
            Schemas\Components\Section::make('Condições')
                ->collapsed()
                ->schema([
                    Forms\Components\KeyValue::make('trigger_conditions')
                        ->label('Condições do Gatilho')
                        ->helperText('Condições adicionais para disparar a automação.'),
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
                Tables\Columns\TextColumn::make('trigger_type')
                    ->label('Gatilho')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'new_conversation' => 'Nova conversa',
                        'new_lead' => 'Novo lead',
                        'new_ticket' => 'Novo ticket',
                        'message_received' => 'Msg recebida',
                        'lead_stage_changed' => 'Mudança etapa',
                        'ticket_overdue' => 'Ticket vencido',
                        default => $state,
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('executions_count')
                    ->label('Execuções')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->actions([Actions\EditAction::make()])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAutomations::route('/'),
            'create' => Pages\CreateAutomation::route('/create'),
            'edit' => Pages\EditAutomation::route('/{record}/edit'),
        ];
    }
}
