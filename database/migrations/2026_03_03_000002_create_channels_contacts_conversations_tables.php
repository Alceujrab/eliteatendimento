<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Canais de atendimento configurados
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // whatsapp_meta, whatsapp_evolution, facebook, instagram, telegram, email, webchat, sms
            $table->string('name');
            $table->string('identifier')->nullable(); // Número, page_id, etc
            $table->json('credentials')->nullable(); // Tokens, API keys (criptografados)
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Contatos / Clientes
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('cpf', 14)->nullable();
            $table->string('avatar')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 2)->nullable();
            $table->json('custom_fields')->nullable();
            $table->json('tags')->nullable();
            $table->string('source')->nullable(); // site, whatsapp, facebook, instagram, indicação
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'phone']);
            $table->index(['tenant_id', 'email']);
        });

        // Conversas
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('new'); // new, open, pending, resolved, archived
            $table->string('priority')->default('medium'); // low, medium, high, urgent
            $table->string('channel_conversation_id')->nullable(); // ID da conversa no canal externo
            $table->text('last_message_preview')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->integer('unread_count')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'assigned_to']);
            $table->index(['tenant_id', 'channel_id']);
        });

        // Mensagens
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Atendente que enviou
            $table->string('type')->default('text'); // text, image, video, audio, document, location, contact, template, system
            $table->text('body')->nullable();
            $table->json('attachments')->nullable(); // [{url, mime_type, filename, size}]
            $table->string('direction'); // inbound, outbound
            $table->string('status')->default('sent'); // sent, delivered, read, failed
            $table->string('external_id')->nullable(); // ID da msg no canal externo
            $table->json('metadata')->nullable();
            $table->boolean('is_internal_note')->default(false);
            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
        });

        // Templates de resposta rápida
        Schema::create('quick_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('body');
            $table->string('shortcut')->nullable(); // /saudacao, /preco
            $table->string('category')->nullable();
            $table->boolean('is_global')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quick_replies');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('channels');
    }
};
