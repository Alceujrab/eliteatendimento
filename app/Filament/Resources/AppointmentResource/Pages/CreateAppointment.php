<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Filament\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Services\AppointmentAvailabilityService;
use App\Services\AppointmentFlowService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CreateAppointment extends CreateRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tenant = filament()->getTenant();

        $data['tenant_id'] = $tenant->id;
        $data['duration_minutes'] = (int) ($data['duration_minutes'] ?? 60);

        $this->ensureWithinAvailability($data);
        $this->ensureNoConflict($data);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        app(AppointmentFlowService::class)->registerCreated($this->record, Auth::id());
    }

    private function ensureNoConflict(array $data): void
    {
        $hasConflict = Appointment::hasConflict(
            tenantId: (int) $data['tenant_id'],
            userId: isset($data['user_id']) ? (int) $data['user_id'] : null,
            contactId: (int) $data['contact_id'],
            scheduledAt: (string) $data['scheduled_at'],
            durationMinutes: (int) ($data['duration_minutes'] ?? 60),
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
