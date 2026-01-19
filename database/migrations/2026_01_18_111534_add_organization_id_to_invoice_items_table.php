<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            // Add organization_id column
            $table->foreignId('organization_id')
                ->nullable()
                ->after('uuid')
                ->constrained() // references id on organizations table
                ->cascadeOnUpdate()
                ->cascadeOnDelete(); // optional, remove if you don't want cascading deletes
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organization_id');
        });
    }
};
