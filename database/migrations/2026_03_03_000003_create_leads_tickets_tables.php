<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Leads e Pipeline de Vendas
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('conversation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('stage')->default('new'); // new, qualified, proposal, negotiation, won, lost
            $table->string('temperature')->default('warm'); // hot, warm, cold
            $table->decimal('estimated_value', 12, 2)->nullable();
            $table->string('vehicle_interest')->nullable(); // Veículo de interesse
            $table->text('notes')->nullable();
            $table->string('source')->nullable(); // site, whatsapp, facebook, instagram, indicação, olx, webmotors
            $table->string('lost_reason')->nullable();
            $table->timestamp('next_follow_up')->nullable();
            $table->timestamp('won_at')->nullable();
            $table->timestamp('lost_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'stage']);
            $table->index(['tenant_id', 'assigned_to']);
        });

        // Atividades do Lead (follow-ups, ligações, etc)
        Schema::create('lead_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // note, call, email, whatsapp, meeting, follow_up, stage_change
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
        });

        // Tickets
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('conversation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('number')->unique(); // TK-00001
            $table->string('subject');
            $table->text('description')->nullable();
            $table->string('category'); // duvida, reclamacao, solicitacao, pos_venda, financeiro
            $table->string('priority')->default('medium'); // low, medium, high, urgent
            $table->string('status')->default('open'); // open, in_progress, waiting, resolved, closed
            $table->timestamp('due_at')->nullable(); // SLA deadline
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->json('tags')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'category']);
        });

        // Comentários de Tickets
        Schema::create('ticket_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('body');
            $table->json('attachments')->nullable();
            $table->boolean('is_internal')->default(false);
            $table->timestamps();
        });

        // SLA Policies
        Schema::create('sla_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('category')->nullable(); // Aplicar a categoria específica
            $table->string('priority')->nullable(); // Aplicar a prioridade específica
            $table->integer('first_response_minutes')->default(60);
            $table->integer('resolution_minutes')->default(480);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_policies');
        Schema::dropIfExists('ticket_comments');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('lead_activities');
        Schema::dropIfExists('leads');
    }
};
