<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            // Add patient_id after visit_id
            $table->foreignId('patient_id')
                ->after('visit_id')
                ->constrained()
                ->onDelete('cascade');

            // Change record_type to varchar
            $table->string('record_type')->change();
        });

        // Ensure no duplicate constraint
        DB::statement("ALTER TABLE medical_records DROP CONSTRAINT IF EXISTS record_type_check");

        // Add CHECK constraint for allowed record types
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

    public function down(): void
    {
        // Drop the CHECK constraint safely
        DB::statement("ALTER TABLE medical_records DROP CONSTRAINT IF EXISTS record_type_check");

        Schema::table('medical_records', function (Blueprint $table) {
            // Drop patient_id foreign key and column
            $table->dropConstrainedForeignId('patient_id');

            // Revert record_type to original allowed values
            $table->string('record_type')->change();
        });

        // Restore the original constraint (only the initial set of values)
        DB::statement("
            ALTER TABLE medical_records
            ADD CONSTRAINT record_type_check
            CHECK (record_type IN (
                'progress_note',
                'diagnosis',
                'procedure',
                'lab_order',
                'imaging_order'
            ));
        ");
    }
};
