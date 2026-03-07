<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id', 'brand', 'model', 'version', 'year_manufacture', 'year_model',
        'color', 'fuel_type', 'transmission', 'mileage', 'price', 'fipe_price',
        'plate', 'chassis', 'renavam', 'description', 'features', 'photos',
        'status', 'condition', 'external_source', 'external_id', 'last_synced_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'fipe_price' => 'decimal:2',
        'features' => 'array',
        'photos' => 'array',
        'last_synced_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /* ── Helpers ── */

    public function getFullNameAttribute(): string
    {
        return "{$this->brand} {$this->model}" . ($this->version ? " {$this->version}" : '');
    }

    public function getYearDisplayAttribute(): string
    {
        return "{$this->year_manufacture}/{$this->year_model}";
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'R$ ' . number_format($this->price, 2, ',', '.');
    }

    public function getFormattedMileageAttribute(): string
    {
        return number_format($this->mileage, 0, '', '.') . ' km';
    }

    public function getMainPhotoAttribute(): ?string
    {
        $photos = $this->photos ?? [];
        return !empty($photos) ? $photos[0]['url'] ?? null : null;
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'available' => ['label' => 'Disponível', 'color' => 'green'],
            'reserved' => ['label' => 'Reservado', 'color' => 'yellow'],
            'sold' => ['label' => 'Vendido', 'color' => 'red'],
            default => ['label' => $this->status, 'color' => 'gray'],
        };
    }

    public function getFuelLabelAttribute(): string
    {
        return match ($this->fuel_type) {
            'flex' => 'Flex',
            'gasolina' => 'Gasolina',
            'diesel' => 'Diesel',
            'eletrico' => 'Elétrico',
            'hibrido' => 'Híbrido',
            default => $this->fuel_type ?? '-',
        };
    }

    public function getTransmissionLabelAttribute(): string
    {
        return match ($this->transmission) {
            'manual' => 'Manual',
            'automatico' => 'Automático',
            'cvt' => 'CVT',
            default => $this->transmission ?? '-',
        };
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }
}
