<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Filament\Resources\AppointmentResource;
use App\Filament\Resources\AppointmentResource\Widgets\TopSellersRanking;
use App\Services\AppointmentsSellerMetricsService;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Carbon;

class ListAppointments extends ListRecords
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            TopSellersRanking::class,
        ];
    }

    protected function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('exportBySeller')
                ->label('Exportar Vendedores (CSV)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->form([
                    Forms\Components\Select::make('days')
                        ->label('Período')
                        ->options([
                            7 => 'Últimos 7 dias',
                            30 => 'Últimos 30 dias',
                            90 => 'Últimos 90 dias',
                        ])
                        ->default(30)
                        ->required()
                        ->native(false),
                ])
                ->action(function (array $data) {
                    $tenant = filament()->getTenant();
                    $days = (int) ($data['days'] ?? 30);

                    $rows = app(AppointmentsSellerMetricsService::class)
                        ->build($tenant->id, $days, null);

                    $filename = 'agenda-vendedores-' . Carbon::now()->format('Ymd-His') . '.csv';

                    return response()->streamDownload(function () use ($rows): void {
                        $handle = fopen('php://output', 'w');

                        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

                        fputcsv($handle, [
                            'Vendedor',
                            'Volume',
                            'Base Comparecimento',
                            'Realizados',
                            'Comparecimento (%)',
                            'Base Conversão',
                            'Leads Ganhos',
                            'Conversão (%)',
                        ], ';');

                        foreach ($rows as $row) {
                            fputcsv($handle, [
                                $row['user'],
                                $row['volume'],
                                $row['attendance_base'],
                                $row['completed'],
                                number_format((float) $row['attendance_rate'], 1, ',', '.'),
                                $row['conversion_base'],
                                $row['won_leads'],
                                number_format((float) $row['conversion_rate'], 1, ',', '.'),
                            ], ';');
                        }

                        fclose($handle);
                    }, $filename, [
                        'Content-Type' => 'text/csv; charset=UTF-8',
                    ]);
                }),
            Actions\CreateAction::make()->label('Novo Agendamento'),
        ];
    }
}
