<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Services\AppointmentFlowService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendAppointmentRemindersCommand extends Command
{
    protected $signature = 'appointments:send-reminders';

    protected $description = 'Registra lembretes automáticos de agendamentos próximos';

    public function handle(AppointmentFlowService $flowService): int
    {
        $now = Carbon::now();
        $windowEnd = $now->copy()->addMinutes(60);

        $appointments = Appointment::query()
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->whereNull('reminder_sent_at')
            ->whereBetween('scheduled_at', [$now, $windowEnd])
            ->get();

        $processed = 0;

        foreach ($appointments as $appointment) {
            $flowService->registerReminder($appointment);
            $appointment->update(['reminder_sent_at' => Carbon::now()]);
            $processed++;
        }

        $this->info("Lembretes processados: {$processed}");

        return self::SUCCESS;
    }
}
