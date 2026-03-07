<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (! Schema::hasColumn('appointments', 'lead_id')) {
                $table->foreignId('lead_id')->nullable()->after('contact_id')->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('appointments', 'duration_minutes')) {
                $table->unsignedSmallInteger('duration_minutes')->default(60)->after('scheduled_at');
            }

            $table->index(['tenant_id', 'scheduled_at']);
            $table->index(['tenant_id', 'user_id', 'scheduled_at']);
            $table->index(['tenant_id', 'status', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'scheduled_at']);
            $table->dropIndex(['tenant_id', 'user_id', 'scheduled_at']);
            $table->dropIndex(['tenant_id', 'status', 'scheduled_at']);

            if (Schema::hasColumn('appointments', 'lead_id')) {
                $table->dropConstrainedForeignId('lead_id');
            }

            if (Schema::hasColumn('appointments', 'duration_minutes')) {
                $table->dropColumn('duration_minutes');
            }
        });
    }
};
