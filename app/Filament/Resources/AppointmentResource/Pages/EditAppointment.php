<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Filament\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Services\AppointmentAvailabilityService;
use App\Services\AppointmentFlowService;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class EditAppointment extends EditRecord
{
    protected static string $resource = AppointmentResource::class;

    private ?string $oldStatus = null;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->oldStatus = (string) $this->record->status;
        $data['duration_minutes'] = (int) ($data['duration_minutes'] ?? 60);

        $this->ensureWithinAvailability($data);
        $this->ensureNoConflict($data);

        return $data;
    }

    protected function afterSave(): void
    {
        app(AppointmentFlowService::class)->registerStatusChange(
            $this->record,
            $this->oldStatus ?? (string) $this->record->status,
            (string) $this->record->status,
            Auth::id(),
        );
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    private function ensureNoConflict(array $data): void
    {
        $tenant = filament()->getTenant();

        $hasConflict = Appointment::hasConflict(
            tenantId: (int) $tenant->id,
            userId: isset($data['user_id']) ? (int) $data['user_id'] : null,
            contactId: (int) $data['contact_id'],
            scheduledAt: (string) $data['scheduled_at'],
            durationMinutes: (int) ($data['duration_minutes'] ?? 60),
            ignoreAppointmentId: (int) $this->record->id,
        );

        if ($hasConflict) {
            throw ValidationException::withMessages([
                'scheduled_at' => 'Já existe outro agendamento no mesmo horário para este responsável.',
            ]);
        }
    }

    private function ensureWithinAvailability(array $data): void
    {
        $tenant = filament()->getTenant();

        $message = app(AppointmentAvailabilityService::class)->assertWithinAvailability(
            tenant: $tenant,
            userId: (int) $data['user_id'],
            scheduledAt: (string) $data['scheduled_at'],
            durationMinutes: (int) ($data['duration_minutes'] ?? 60),
        );

        if ($message) {
            throw ValidationException::withMessages([
                'scheduled_at' => $message,
            ]);
        }
    }
}
