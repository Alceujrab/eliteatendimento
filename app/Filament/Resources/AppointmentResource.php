<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppointmentResource\Pages;
use App\Models\Appointment;
use App\Services\AppointmentAvailabilityService;
use App\Services\AppointmentFlowService;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-calendar-days';
    protected static string | \UnitEnum | null $navigationGroup = 'Vendas';
    protected static ?string $navigationLabel = 'Agenda';
    protected static ?string $modelLabel = 'Agendamento';
    protected static ?string $pluralModelLabel = 'Agenda';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Schemas\Components\Section::make('Compromisso')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('contact_id')
                        ->label('Contato')
                        ->relationship('contact', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\Select::make('lead_id')
                        ->label('Lead (opcional)')
                        ->relationship('lead', 'id')
                        ->searchable()
                        ->preload()
                        ->getOptionLabelFromRecordUsing(fn ($record) => "#{$record->id} - {$record->stage_label}"),
                    Forms\Components\Select::make('user_id')
                        ->label('Responsável')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\Select::make('vehicle_id')
                        ->label('Veículo (opcional)')
                        ->relationship('vehicle', 'model')
                        ->searchable()
                        ->preload()
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name),
                    Forms\Components\Select::make('type')
                        ->label('Tipo')
                        ->options([
                            'test_drive' => 'Test Drive',
                            'visit' => 'Visita',
                            'delivery' => 'Entrega',
                            'maintenance' => 'Manutenção',
                        ])
                        ->default('visit')
                        ->native(false)
                        ->required(),
                    Forms\Components\DateTimePicker::make('scheduled_at')
                        ->label('Data e horário')
                        ->seconds(false)
                        ->required()
                        ->native(false),
                    Forms\Components\TextInput::make('duration_minutes')
                        ->label('Duração (min)')
                        ->numeric()
                        ->default(60)
                        ->minValue(15)
                        ->maxValue(480)
                        ->required(),
                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            'scheduled' => 'Agendado',
                            'confirmed' => 'Confirmado',
                            'completed' => 'Realizado',
                            'cancelled' => 'Cancelado',
                            'no_show' => 'Não Compareceu',
                        ])
                        ->default('scheduled')
                        ->native(false)
                        ->required(),
                    Forms\Components\Textarea::make('notes')
                        ->label('Observações')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Data/Hora')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Duração')
                    ->suffix(' min')
                    ->sortable(),
                Tables\Columns\TextColumn::make('contact.name')
                    ->label('Contato')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Responsável')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vehicle.full_name')
                    ->label('Veículo')
                    ->limit(30)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'test_drive' => 'Test Drive',
                        'visit' => 'Visita',
                        'delivery' => 'Entrega',
                        'maintenance' => 'Manutenção',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled' => 'gray',
                        'confirmed' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'no_show' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'scheduled' => 'Agendado',
                        'confirmed' => 'Confirmado',
                        'completed' => 'Realizado',
                        'cancelled' => 'Cancelado',
                        'no_show' => 'Não Compareceu',
                        default => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\Filter::make('today')
                    ->label('Hoje')
                    ->query(fn (Builder $query): Builder => $query->whereDate('scheduled_at', Carbon::today())),
                Tables\Filters\Filter::make('week')
                    ->label('Próximos 7 dias')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('scheduled_at', [Carbon::now(), Carbon::now()->addDays(7)])),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'scheduled' => 'Agendado',
                        'confirmed' => 'Confirmado',
                        'completed' => 'Realizado',
                        'cancelled' => 'Cancelado',
                        'no_show' => 'Não Compareceu',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'test_drive' => 'Test Drive',
                        'visit' => 'Visita',
                        'delivery' => 'Entrega',
                        'maintenance' => 'Manutenção',
                    ]),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Responsável')
                    ->relationship('user', 'name'),
            ])
            ->actions([
                Actions\Action::make('reschedule')
                    ->label('Reagendar')
                    ->icon('heroicon-o-clock')
                    ->color('primary')
                    ->visible(fn (Appointment $record): bool => in_array($record->status, ['scheduled', 'confirmed', 'no_show']))
                    ->fillForm(fn (Appointment $record): array => [
                        'scheduled_at' => optional($record->scheduled_at)?->format('Y-m-d H:i:s'),
                        'duration_minutes' => (int) ($record->duration_minutes ?? 60),
                    ])
                    ->form([
                        Forms\Components\DateTimePicker::make('scheduled_at')
                            ->label('Novo horário')
                            ->required()
                            ->seconds(false)
                            ->native(false),
                        Forms\Components\TextInput::make('duration_minutes')
                            ->label('Nova duração (min)')
                            ->numeric()
                            ->required()
                            ->minValue(15)
                            ->maxValue(480),
                    ])
                    ->action(function (Appointment $record, array $data, AppointmentFlowService $flowService): void {
                        $tenant = filament()->getTenant();
                        $newStart = Carbon::parse((string) $data['scheduled_at']);
                        $newDuration = (int) ($data['duration_minutes'] ?? 60);

                        $availabilityMessage = app(AppointmentAvailabilityService::class)->assertWithinAvailability(
                            tenant: $tenant,
                            userId: (int) $record->user_id,
                            scheduledAt: $newStart,
                            durationMinutes: $newDuration,
                        );

                        if ($availabilityMessage) {
                            throw ValidationException::withMessages([
                                'scheduled_at' => $availabilityMessage,
                            ]);
                        }

                        $hasConflict = Appointment::hasConflict(
                            tenantId: (int) $tenant->id,
                            userId: (int) $record->user_id,
                            contactId: (int) $record->contact_id,
                            scheduledAt: $newStart,
                            durationMinutes: $newDuration,
                            ignoreAppointmentId: (int) $record->id,
                        );

                        if ($hasConflict) {
                            throw ValidationException::withMessages([
                                'scheduled_at' => 'Já existe outro agendamento no mesmo horário para este responsável.',
                            ]);
                        }

                        $oldStatus = $record->status;

                        $record->update([
                            'scheduled_at' => $newStart,
                            'duration_minutes' => $newDuration,
                            'status' => 'scheduled',
                            'reminder_sent_at' => null,
                        ]);

                        $flowService->registerStatusChange($record->fresh(), $oldStatus, 'scheduled', Auth::id());
                    }),
                Actions\Action::make('confirm')
                    ->label('Confirmar')
                    ->icon('heroicon-o-check')
                    ->color('info')
                    ->visible(fn (Appointment $record): bool => $record->status === 'scheduled')
                    ->action(function (Appointment $record, AppointmentFlowService $flowService): void {
                        $oldStatus = $record->status;
                        $record->update(['status' => 'confirmed']);
                        $flowService->registerStatusChange($record->fresh(), $oldStatus, 'confirmed', Auth::id());
                    }),
                Actions\Action::make('complete')
                    ->label('Concluir')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Appointment $record): bool => in_array($record->status, ['scheduled', 'confirmed']))
                    ->action(function (Appointment $record, AppointmentFlowService $flowService): void {
                        $oldStatus = $record->status;
                        $record->update(['status' => 'completed']);
                        $flowService->registerStatusChange($record->fresh(), $oldStatus, 'completed', Auth::id());
                    }),
                Actions\Action::make('no_show')
                    ->label('No-show')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('warning')
                    ->visible(fn (Appointment $record): bool => in_array($record->status, ['scheduled', 'confirmed']))
                    ->action(function (Appointment $record, AppointmentFlowService $flowService): void {
                        $oldStatus = $record->status;
                        $record->update(['status' => 'no_show']);
                        if ($record->lead_id) {
                            $record->lead()->update(['next_follow_up' => Carbon::now()->addDay()]);
                        }
                        $flowService->registerStatusChange($record->fresh(), $oldStatus, 'no_show', Auth::id());
                    }),
                Actions\Action::make('cancel')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Appointment $record): bool => in_array($record->status, ['scheduled', 'confirmed']))
                    ->action(function (Appointment $record, AppointmentFlowService $flowService): void {
                        $oldStatus = $record->status;
                        $record->update(['status' => 'cancelled']);
                        $flowService->registerStatusChange($record->fresh(), $oldStatus, 'cancelled', Auth::id());
                    }),
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('scheduled_at', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $tenant = filament()->getTenant();

        if (! $tenant) {
            return null;
        }

        $count = static::getModel()::query()
            ->where('tenant_id', $tenant->id)
            ->whereDate('scheduled_at', Carbon::today())
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string | array | null
    {
        return 'warning';
    }
}
