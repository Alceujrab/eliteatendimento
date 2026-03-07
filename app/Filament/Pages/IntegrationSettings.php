<?php

namespace App\Filament\Pages;

use App\Models\IntegrationSetting;
use App\Services\VehicleFeedSyncService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Http;

class IntegrationSettings extends Page
{
    protected string $view = 'filament.pages.integration-settings';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-puzzle-piece';
    protected static ?string $navigationLabel = 'Integrações';
    protected static ?string $title = 'Configurações de Integrações';
    protected static string | \UnitEnum | null $navigationGroup = 'Configurações';
    protected static ?int $navigationSort = 10;

    /* ── Evolution API ── */
    public ?string $evo_base_url = null;
    public ?string $evo_api_key = null;
    public ?string $evo_webhook_url = null;
    public ?string $evo_webhook_secret = null;
    public bool $evo_is_active = true;

    /* ── Meta Platform (Facebook/Instagram) ── */
    public ?string $meta_app_id = null;
    public ?string $meta_app_secret = null;
    public ?string $meta_verify_token = null;
    public bool $meta_is_active = true;

    /* ── Feed de Estoque (Revenda Mais XML) ── */
    public ?string $vehicle_feed_url = null;
    public bool $vehicle_feed_is_active = true;

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::FourExtraLarge;
    }

    public function mount(): void
    {
        $tenant = filament()->getTenant();

        // Load Evolution settings
        $evo = IntegrationSetting::forTenant($tenant->id, 'evolution');
        if ($evo) {
            $this->evo_base_url = $evo->credential('base_url');
            $this->evo_api_key = $evo->credential('api_key');
            $this->evo_webhook_url = $evo->setting('webhook_url');
            $this->evo_webhook_secret = $evo->credential('webhook_secret');
            $this->evo_is_active = $evo->is_active;
        }

        // Load Meta settings
        $meta = IntegrationSetting::forTenant($tenant->id, 'meta');
        if ($meta) {
            $this->meta_app_id = $meta->credential('app_id');
            $this->meta_app_secret = $meta->credential('app_secret');
            $this->meta_verify_token = $meta->credential('verify_token');
            $this->meta_is_active = $meta->is_active;
        } else {
            // Generate a default verify token
            $this->meta_verify_token = 'elite-' . strtolower(str_replace(' ', '-', $tenant->name)) . '-' . random_int(1000, 9999);
        }

        $vehicleFeed = IntegrationSetting::forTenant($tenant->id, VehicleFeedSyncService::PROVIDER);

        if ($vehicleFeed) {
            $this->vehicle_feed_url = $vehicleFeed->setting('feed_url');
            $this->vehicle_feed_is_active = $vehicleFeed->is_active;
        }
    }

    /* ────────────────────────────────────────────── */
    /*  Actions                                        */
    /* ────────────────────────────────────────────── */

    public function saveEvolution(): void
    {
        $this->validate([
            'evo_base_url' => 'required|url',
            'evo_api_key'  => 'required|string|min:5',
        ], [
            'evo_base_url.required' => 'A URL da Evolution API é obrigatória.',
            'evo_base_url.url' => 'Informe uma URL válida (ex: https://evolution.seuservidor.com).',
            'evo_api_key.required' => 'A API Key é obrigatória.',
        ]);

        $tenant = filament()->getTenant();

        IntegrationSetting::updateOrCreate(
            ['tenant_id' => $tenant->id, 'provider' => 'evolution'],
            [
                'credentials' => [
                    'base_url'       => rtrim($this->evo_base_url, '/'),
                    'api_key'        => $this->evo_api_key,
                    'webhook_secret' => $this->evo_webhook_secret,
                ],
                'settings' => [
                    'webhook_url' => $this->evo_webhook_url,
                ],
                'is_active' => $this->evo_is_active,
            ]
        );

        Notification::make()
            ->success()
            ->title('Evolution API salva')
            ->body('As configurações da Evolution API foram salvas com sucesso.')
            ->send();
    }

    public function testEvolution(): void
    {
        if (! $this->evo_base_url || ! $this->evo_api_key) {
            Notification::make()->danger()->title('Preencha a URL e API Key primeiro.')->send();
            return;
        }

        try {
            $response = Http::withHeaders(['apikey' => $this->evo_api_key])
                ->timeout(10)
                ->get(rtrim($this->evo_base_url, '/') . '/instance/fetchInstances');

            if ($response->successful()) {
                $count = count($response->json() ?? []);
                Notification::make()
                    ->success()
                    ->title('Conexão OK!')
                    ->body("Evolution API respondeu. {$count} instância(s) encontrada(s).")
                    ->send();
            } else {
                Notification::make()
                    ->danger()
                    ->title('Erro na conexão')
                    ->body("Status {$response->status()}: " . ($response->json('message') ?? $response->body()))
                    ->send();
            }
        } catch (\Throwable $e) {
            Notification::make()
                ->danger()
                ->title('Falha na conexão')
                ->body($e->getMessage())
                ->send();
        }
    }

    public function saveMeta(): void
    {
        $this->validate([
            'meta_app_id'     => 'required|string',
            'meta_app_secret' => 'required|string|min:10',
            'meta_verify_token' => 'required|string|min:5',
        ], [
            'meta_app_id.required' => 'O App ID é obrigatório.',
            'meta_app_secret.required' => 'O App Secret é obrigatório.',
            'meta_verify_token.required' => 'O Verify Token é obrigatório.',
        ]);

        $tenant = filament()->getTenant();

        IntegrationSetting::updateOrCreate(
            ['tenant_id' => $tenant->id, 'provider' => 'meta'],
            [
                'credentials' => [
                    'app_id'       => $this->meta_app_id,
                    'app_secret'   => $this->meta_app_secret,
                    'verify_token' => $this->meta_verify_token,
                ],
                'settings' => [],
                'is_active' => $this->meta_is_active,
            ]
        );

        Notification::make()
            ->success()
            ->title('Meta Platform salva')
            ->body('As configurações do Facebook/Instagram foram salvas com sucesso.')
            ->send();
    }

    public function saveVehicleFeed(): void
    {
        $this->validate([
            'vehicle_feed_url' => 'required|url',
        ], [
            'vehicle_feed_url.required' => 'A URL do feed XML é obrigatória.',
            'vehicle_feed_url.url' => 'Informe uma URL válida para o feed.',
        ]);

        $tenant = filament()->getTenant();

        IntegrationSetting::updateOrCreate(
            ['tenant_id' => $tenant->id, 'provider' => VehicleFeedSyncService::PROVIDER],
            [
                'credentials' => [],
                'settings' => [
                    'feed_url' => trim((string) $this->vehicle_feed_url),
                ],
                'is_active' => $this->vehicle_feed_is_active,
            ]
        );

        Notification::make()
            ->success()
            ->title('Feed XML salvo')
            ->body('Configuração do feed de estoque salva com sucesso.')
            ->send();
    }

    public function testVehicleFeed(): void
    {
        $this->validate([
            'vehicle_feed_url' => 'required|url',
        ]);

        try {
            $response = Http::timeout(15)->get((string) $this->vehicle_feed_url);

            if (! $response->successful()) {
                Notification::make()
                    ->danger()
                    ->title('Falha ao consultar feed')
                    ->body('HTTP ' . $response->status())
                    ->send();
                return;
            }

            $xml = @simplexml_load_string($response->body());

            if (! $xml) {
                Notification::make()
                    ->danger()
                    ->title('XML inválido')
                    ->body('Não foi possível interpretar o XML informado.')
                    ->send();
                return;
            }

            $total = isset($xml->AD) ? count($xml->AD) : 0;

            Notification::make()
                ->success()
                ->title('Feed válido')
                ->body("Conexão OK. {$total} veículo(s) encontrado(s) no XML.")
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->danger()
                ->title('Erro no teste do feed')
                ->body($e->getMessage())
                ->send();
        }
    }
}
