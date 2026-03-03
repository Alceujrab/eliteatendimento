<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Campanhas de marketing
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type'); // whatsapp, sms, email
            $table->string('status')->default('draft'); // draft, scheduled, running, paused, completed, cancelled
            $table->text('message_template')->nullable();
            $table->json('media')->nullable(); // Mídia anexada
            $table->json('audience_filter')->nullable(); // Filtros de segmentação
            $table->integer('total_recipients')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('delivered_count')->default(0);
            $table->integer('read_count')->default(0);
            $table->integer('replied_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Log de envios da campanha
        Schema::create('campaign_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending, sent, delivered, read, replied, failed
            $table->string('external_id')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // Catálogo de Veículos
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('brand'); // Marca
            $table->string('model'); // Modelo
            $table->string('version')->nullable(); // Versão
            $table->integer('year_manufacture'); // Ano fabricação
            $table->integer('year_model'); // Ano modelo
            $table->string('color')->nullable();
            $table->string('fuel_type')->nullable(); // flex, gasolina, diesel, eletrico, hibrido
            $table->string('transmission')->nullable(); // manual, automatico, cvt
            $table->integer('mileage')->nullable(); // KM rodados
            $table->decimal('price', 12, 2);
            $table->decimal('fipe_price', 12, 2)->nullable();
            $table->string('plate', 10)->nullable();
            $table->string('chassis', 20)->nullable();
            $table->string('renavam', 15)->nullable();
            $table->text('description')->nullable();
            $table->json('features')->nullable(); // Air bag, ABS, etc
            $table->json('photos')->nullable(); // [{url, order}]
            $table->string('status')->default('available'); // available, reserved, sold
            $table->string('condition')->default('seminovo'); // novo, seminovo
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'brand', 'model']);
        });

        // Agendamentos (test-drive, visitas)
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Vendedor responsável
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // test_drive, visit, delivery, maintenance
            $table->dateTime('scheduled_at');
            $table->string('status')->default('scheduled'); // scheduled, confirmed, completed, cancelled, no_show
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Pesquisas de Satisfação (NPS/CSAT)
        Schema::create('satisfaction_surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('conversation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ticket_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // nps, csat
            $table->integer('score'); // NPS: 0-10, CSAT: 1-5
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        // Base de Conhecimento
        Schema::create('knowledge_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->text('body');
            $table->string('category'); // financiamento, documentacao, garantias, procedimentos
            $table->json('tags')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_internal')->default(true); // Só para atendentes
            $table->integer('views_count')->default(0);
            $table->integer('helpful_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Automações (registro de fluxos n8n)
        Schema::create('automations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('trigger_type'); // new_conversation, keyword, schedule, webhook
            $table->json('trigger_conditions')->nullable();
            $table->string('n8n_workflow_id')->nullable();
            $table->string('n8n_webhook_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('executions_count')->default(0);
            $table->timestamps();
        });

        // Logs de Auditoria
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action'); // created, updated, deleted, login, logout, etc
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'created_at']);
            $table->index(['model_type', 'model_id']);
        });

        // Notificações internas
        Schema::create('notifications_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // new_conversation, sla_warning, new_lead, mention, etc
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('link')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications_log');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('automations');
        Schema::dropIfExists('knowledge_articles');
        Schema::dropIfExists('satisfaction_surveys');
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('campaign_messages');
        Schema::dropIfExists('campaigns');
    }
};
