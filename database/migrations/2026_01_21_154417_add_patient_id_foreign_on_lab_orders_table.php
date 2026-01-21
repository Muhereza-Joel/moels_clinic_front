<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lab_orders', function (Blueprint $table) {
            // Add patient_id column
            $table->unsignedBigInteger('patient_id')->nullable();

            // Add foreign key constraint if patients table exists
            $table->foreign('patient_id')
                ->references('id')
                ->on('patients')
                ->onDelete('cascade');
        });

        // ⚠️ Note: PostgreSQL does not support "AFTER visit_id" like MySQL.
        // Column order is not guaranteed in Postgres, but Laravel/Eloquent
        // works by column names, so this is fine.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lab_orders', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);
            $table->dropColumn('patient_id');
        });
    }
};
