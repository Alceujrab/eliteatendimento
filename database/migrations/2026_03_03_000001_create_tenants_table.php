<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nome da concessionária
            $table->string('slug')->unique();
            $table->string('cnpj', 18)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 2)->nullable();
            $table->string('logo')->nullable();
            $table->json('business_hours')->nullable(); // {"mon":{"open":"08:00","close":"18:00"}, ...}
            $table->json('settings')->nullable(); // Configurações gerais do tenant
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Adicionar tenant_id à tabela users
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('role')->default('vendedor'); // admin, gestor, vendedor, atendente
            $table->string('phone', 20)->nullable();
            $table->string('avatar')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_online')->default(false);
            $table->integer('max_concurrent_chats')->default(5);
            $table->timestamp('last_seen_at')->nullable();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn(['tenant_id', 'role', 'phone', 'avatar', 'is_active', 'is_online', 'max_concurrent_chats', 'last_seen_at', 'deleted_at']);
        });
        Schema::dropIfExists('tenants');
    }
};
