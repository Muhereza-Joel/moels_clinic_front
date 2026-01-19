<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        // Drop unique constraints if they exist
        DB::statement('ALTER TABLE cpt_codes DROP CONSTRAINT IF EXISTS cpt_codes_code_unique');
        DB::statement('ALTER TABLE icd10_codes DROP CONSTRAINT IF EXISTS icd10_codes_code_unique');

        Schema::table('cpt_codes', function (Blueprint $table) {
            $table->string('code')->change(); // keep column as string, no unique
        });

        Schema::table('icd10_codes', function (Blueprint $table) {
            $table->string('code')->change(); // keep column as string, no unique
        });
    }

    public function down(): void
    {
        // Clean duplicates before re‑adding unique
        DB::statement("
            DELETE FROM cpt_codes a
            USING cpt_codes b
            WHERE a.ctid < b.ctid AND a.code = b.code;
        ");

        DB::statement("
            DELETE FROM icd10_codes a
            USING icd10_codes b
            WHERE a.ctid < b.ctid AND a.code = b.code;
        ");

        // Drop existing constraints if they somehow still exist
        DB::statement('ALTER TABLE cpt_codes DROP CONSTRAINT IF EXISTS cpt_codes_code_unique');
        DB::statement('ALTER TABLE icd10_codes DROP CONSTRAINT IF EXISTS icd10_codes_code_unique');

        // Re‑add unique constraints with safe names
        DB::statement('ALTER TABLE cpt_codes ADD CONSTRAINT cpt_codes_code_unique UNIQUE (code)');
        DB::statement('ALTER TABLE icd10_codes ADD CONSTRAINT icd10_codes_code_unique UNIQUE (code)');
    }
};
