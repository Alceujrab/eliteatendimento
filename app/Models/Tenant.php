<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'cnpj', 'phone', 'email', 'address',
        'city', 'state', 'logo', 'business_hours', 'settings', 'is_active',
    ];

    protected $casts = [
        'business_hours' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Use slug para route model binding (URLs amigáveis no Filament).
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function channels(): HasMany
    {
        return $this->hasMany(Channel::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function knowledgeArticles(): HasMany
    {
        return $this->hasMany(KnowledgeArticle::class);
    }

    public function automations(): HasMany
    {
        return $this->hasMany(Automation::class);
    }

    public function slaPolicies(): HasMany
    {
        return $this->hasMany(SlaPolicy::class);
    }

    public function quickReplies(): HasMany
    {
        return $this->hasMany(QuickReply::class);
    }

    public function integrationSettings(): HasMany
    {
        return $this->hasMany(IntegrationSetting::class);
    }
}
