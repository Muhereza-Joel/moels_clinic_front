<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop old constraint if it exists
        DB::statement("ALTER TABLE medical_records DROP CONSTRAINT IF EXISTS record_type_check");

        // Ensure column is string type
        Schema::table('medical_records', function (Blueprint $table) {
            $table->string('record_type')->change();
        });

        // Add new CHECK constraint with expanded options
        DB::statement("
            ALTER TABLE medical_records
            ADD CONSTRAINT record_type_check
            CHECK (record_type IN (
                'consultation',
                'progress_note',
                'diagnosis',
                'discharge',
                'procedure',
                'lab_order',
                'imaging_order',
                'lab_report',
                'death',
                'birth',
                'referral'
            ));
        ");
    }

    public function down(): void
    {
        // Drop expanded constraint
        DB::statement("ALTER TABLE medical_records DROP CONSTRAINT IF EXISTS record_type_check");

        // Revert to original allowed values
        DB::statement("
            ALTER TABLE medical_records
            ADD CONSTRAINT record_type_check
            CHECK (record_type IN (
                'progress_note',
                'diagnosis',
                'procedure',
                'lab_order',
                'imaging_order',
                'death',
                'birth',
                'referral'
            ));
        ");
    }
};
