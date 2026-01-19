<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('record_types', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->string('code')->unique(); // e.g. consultation, progress_note
            $table->string('label');          // e.g. Consultation Note
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('medical_records', function (Blueprint $table) {
            // Add new foreign key reference
            $table->foreignId('record_type_id')
                ->nullable()
                ->constrained('record_types')
                ->onDelete('restrict')
                ->after('patient_id');

            // Drop the old column
            $table->dropColumn('record_type');
        });

        // Drop the old CHECK constraint if it exists
        DB::statement("ALTER TABLE medical_records DROP CONSTRAINT IF EXISTS record_type_check");
    }

    public function down(): void
    {
        // Re-add the old column
        Schema::table('medical_records', function (Blueprint $table) {
            $table->string('record_type')->nullable();
            $table->dropConstrainedForeignId('record_type_id');
        });

        // Restore the old CHECK constraint
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

        Schema::dropIfExists('record_types');
    }
};
