<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            // Change entity_id from uuid to unsignedBigInteger
            // Defensive: drop the old column first if it exists
            if (Schema::hasColumn('audit_logs', 'entity_id')) {
                $table->dropColumn('entity_id');
            }
            $table->unsignedBigInteger('entity_id')->nullable();

            $table->string('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->string('http_method', 10)->nullable();
            $table->enum('severity', ['info', 'warning', 'critical'])->default('info');
            $table->uuid('correlation_id')->nullable();
            $table->softDeletes();

            // Helpful composite index for tracing
            $table->index(['correlation_id', 'created_at'], 'audit_logs_correlation_created_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            // Roll back entity_id to uuid
            $table->dropColumn('entity_id');
            $table->uuid('entity_id')->nullable();

            $table->dropColumn(['user_agent', 'url', 'http_method', 'severity', 'correlation_id']);
            $table->dropSoftDeletes();
        });

        // Defensive drop for PostgreSQL
        DB::statement('DROP INDEX IF EXISTS audit_logs_correlation_created_index');
    }
};
