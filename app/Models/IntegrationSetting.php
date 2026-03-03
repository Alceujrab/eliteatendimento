<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegrationSetting extends Model
{
    protected $fillable = [
        'tenant_id', 'provider', 'credentials', 'settings', 'is_active',
    ];

    protected $casts = [
        'credentials' => 'encrypted:array',
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /* ── Helpers ── */

    /**
     * Get a credential value by key.
     */
    public function credential(string $key, mixed $default = null): mixed
    {
        return data_get($this->credentials, $key, $default);
    }

    /**
     * Get a setting value by key.
     */
    public function setting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }

    /* ── Static finders ── */

    /**
     * Get integration settings for a tenant + provider.
     */
    public static function forTenant(int $tenantId, string $provider): ?self
    {
        return static::where('tenant_id', $tenantId)
            ->where('provider', $provider)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get the Evolution API settings for the current Filament tenant.
     */
    public static function evolution(?int $tenantId = null): ?self
    {
        $tenantId ??= filament()->getTenant()?->id;
        return $tenantId ? static::forTenant($tenantId, 'evolution') : null;
    }

    /**
     * Get the Meta Platform settings for the current Filament tenant.
     */
    public static function meta(?int $tenantId = null): ?self
    {
        $tenantId ??= filament()->getTenant()?->id;
        return $tenantId ? static::forTenant($tenantId, 'meta') : null;
    }
}
