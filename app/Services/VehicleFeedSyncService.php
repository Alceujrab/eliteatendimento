<?php

namespace App\Services;

use App\Models\IntegrationSetting;
use App\Models\Tenant;
use App\Models\Vehicle;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VehicleFeedSyncService
{
    public const PROVIDER = 'revendamais_feed';

    public function syncTenant(Tenant $tenant): array
    {
        $setting = IntegrationSetting::forTenant($tenant->id, self::PROVIDER);

        if (! $setting || ! $setting->is_active) {
            return [
                'tenant_id' => $tenant->id,
                'processed' => 0,
                'created' => 0,
                'updated' => 0,
                'deleted' => 0,
                'skipped' => 0,
                'status' => 'inactive',
                'message' => 'Integração de feed está inativa.',
            ];
        }

        $feedUrl = (string) $setting->setting('feed_url');

        if (blank($feedUrl)) {
            return [
                'tenant_id' => $tenant->id,
                'processed' => 0,
                'created' => 0,
                'updated' => 0,
                'deleted' => 0,
                'skipped' => 0,
                'status' => 'invalid',
                'message' => 'URL do feed XML não configurada.',
            ];
        }

        $response = Http::timeout(30)->retry(2, 500)->get($feedUrl);

        if (! $response->successful()) {
            return [
                'tenant_id' => $tenant->id,
                'processed' => 0,
                'created' => 0,
                'updated' => 0,
                'deleted' => 0,
                'skipped' => 0,
                'status' => 'error',
                'message' => 'Falha ao baixar feed XML. HTTP ' . $response->status(),
            ];
        }

        $xml = @simplexml_load_string($response->body());

        if (! $xml) {
            return [
                'tenant_id' => $tenant->id,
                'processed' => 0,
                'created' => 0,
                'updated' => 0,
                'deleted' => 0,
                'skipped' => 0,
                'status' => 'error',
                'message' => 'XML inválido ou malformado.',
            ];
        }

        $processed = 0;
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $externalIds = [];

        foreach ($xml->AD as $ad) {
            $externalId = $this->text($ad, 'ID');
            $brand = Str::title($this->text($ad, 'MAKE'));
            $model = Str::upper(trim($this->text($ad, 'MODEL')));
            $yearModel = (int) $this->text($ad, 'YEAR');
            $yearManufacture = (int) $this->text($ad, 'FABRIC_YEAR');
            $price = $this->number($this->text($ad, 'PRICE'));

            if (blank($externalId) || blank($brand) || blank($model) || $yearModel <= 0 || $yearManufacture <= 0 || $price <= 0) {
                $skipped++;
                continue;
            }

            $externalIds[] = $externalId;
            $processed++;

            $payload = [
                'tenant_id' => $tenant->id,
                'brand' => Str::limit($brand, 255, ''),
                'model' => Str::limit($model, 255, ''),
                'version' => $this->extractVersion($ad),
                'year_manufacture' => $yearManufacture,
                'year_model' => $yearModel,
                'color' => Str::title($this->text($ad, 'COLOR')),
                'fuel_type' => $this->normalizeFuel($this->text($ad, 'FUEL')),
                'transmission' => $this->normalizeGear($this->text($ad, 'GEAR')),
                'mileage' => (int) $this->number($this->text($ad, 'MILEAGE')),
                'price' => $price,
                'plate' => Str::upper($this->text($ad, 'PLATE')),
                'chassis' => Str::upper($this->text($ad, 'CHASSI')),
                'description' => $this->text($ad, 'DESCRIPTION'),
                'features' => $this->parseAccessories($this->text($ad, 'ACCESSORIES')),
                'status' => 'available',
                'condition' => $this->normalizeCondition($this->text($ad, 'CONDITION')),
                'external_source' => self::PROVIDER,
                'last_synced_at' => Carbon::now(),
            ];

            $vehicle = Vehicle::withTrashed()->firstOrNew([
                'tenant_id' => $tenant->id,
                'external_source' => self::PROVIDER,
                'external_id' => $externalId,
            ]);

            $isNew = ! $vehicle->exists;

            $vehicle->fill($payload);
            $vehicle->external_id = $externalId;

            if ($vehicle->trashed()) {
                $vehicle->restore();
            }

            $vehicle->save();

            if ($isNew) {
                $created++;
            } else {
                $updated++;
            }
        }

        $deleted = 0;

        if (! empty($externalIds)) {
            $deleted = Vehicle::where('tenant_id', $tenant->id)
                ->where('external_source', self::PROVIDER)
                ->whereNotIn('external_id', $externalIds)
                ->whereNull('deleted_at')
                ->update([
                    'status' => 'sold',
                    'deleted_at' => Carbon::now(),
                ]);
        }

        return [
            'tenant_id' => $tenant->id,
            'processed' => $processed,
            'created' => $created,
            'updated' => $updated,
            'deleted' => $deleted,
            'skipped' => $skipped,
            'status' => 'ok',
            'message' => "Sincronização concluída: {$processed} processados.",
        ];
    }

    public function syncAllTenants(): array
    {
        $results = [];

        $tenants = Tenant::query()->where('is_active', true)->get();

        foreach ($tenants as $tenant) {
            try {
                $results[] = $this->syncTenant($tenant);
            } catch (\Throwable $exception) {
                Log::error('Falha ao sincronizar feed de veículos.', [
                    'tenant_id' => $tenant->id,
                    'message' => $exception->getMessage(),
                ]);

                $results[] = [
                    'tenant_id' => $tenant->id,
                    'processed' => 0,
                    'created' => 0,
                    'updated' => 0,
                    'deleted' => 0,
                    'skipped' => 0,
                    'status' => 'error',
                    'message' => $exception->getMessage(),
                ];
            }
        }

        return $results;
    }

    private function text(\SimpleXMLElement $node, string $key): string
    {
        return trim((string) ($node->{$key} ?? ''));
    }

    private function number(string $value): float
    {
        if ($value === '') {
            return 0;
        }

        $normalized = str_replace(['R$', 'r$', ' '], '', $value);
        $normalized = str_replace('.', '', $normalized);
        $normalized = str_replace(',', '.', $normalized);

        return (float) $normalized;
    }

    private function normalizeFuel(string $fuel): ?string
    {
        $fuel = Str::lower(trim($fuel));

        return match ($fuel) {
            'flex' => 'flex',
            'gasolina' => 'gasoline',
            'etanol', 'alcool' => 'ethanol',
            'diesel' => 'diesel',
            'elétrico', 'eletrico' => 'electric',
            'híbrido', 'hibrido' => 'hybrid',
            default => null,
        };
    }

    private function normalizeGear(string $gear): ?string
    {
        $gear = Str::lower(trim($gear));

        return match ($gear) {
            'manual' => 'manual',
            'automatico', 'automático' => 'automatic',
            'cvt' => 'cvt',
            default => null,
        };
    }

    private function normalizeCondition(string $condition): string
    {
        $condition = Str::lower(trim($condition));

        return match ($condition) {
            'novo' => 'new',
            default => 'used',
        };
    }

    private function parseAccessories(string $accessories): array
    {
        if (blank($accessories)) {
            return [];
        }

        return collect(explode(',', $accessories))
            ->map(fn (string $item) => trim($item))
            ->filter()
            ->take(50)
            ->values()
            ->all();
    }

    private function extractVersion(\SimpleXMLElement $ad): ?string
    {
        $title = $this->text($ad, 'TITLE');
        $motor = $this->text($ad, 'MOTOR');

        if (blank($title) && blank($motor)) {
            return null;
        }

        $value = trim($motor !== '' ? $motor : $title);

        return Str::limit($value, 100, '');
    }
}
