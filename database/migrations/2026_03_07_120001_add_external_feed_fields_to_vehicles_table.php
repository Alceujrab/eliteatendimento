<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('external_source')->nullable()->after('condition');
            $table->string('external_id')->nullable()->after('external_source');
            $table->timestamp('last_synced_at')->nullable()->after('external_id');

            $table->index(['tenant_id', 'external_source']);
            $table->index(['tenant_id', 'external_source', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'external_source']);
            $table->dropIndex(['tenant_id', 'external_source', 'external_id']);

            $table->dropColumn([
                'external_source',
                'external_id',
                'last_synced_at',
            ]);
        });
    }
};
