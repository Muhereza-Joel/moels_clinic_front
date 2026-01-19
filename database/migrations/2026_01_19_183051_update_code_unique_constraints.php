<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove unique constraint from cpt_codes
        Schema::table('cpt_codes', function (Blueprint $table) {
            $table->string('code')->change(); // drops unique index
        });

        // Remove unique constraint from icd10_codes
        Schema::table('icd10_codes', function (Blueprint $table) {
            $table->string('code')->change(); // drops unique index
        });
    }

    public function down(): void
    {
        // Rollback: re‑add unique to cpt_codes and icd10_codes
        Schema::table('cpt_codes', function (Blueprint $table) {
            $table->string('code')->unique()->change();
        });

        Schema::table('icd10_codes', function (Blueprint $table) {
            $table->string('code')->unique()->change();
        });
    }
};
