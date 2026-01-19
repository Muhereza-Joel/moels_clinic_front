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
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->constrained()->onDelete('restrict');
            $table->foreignId('visit_id')->constrained()->onDelete('cascade');
            $table->enum('record_type', ['progress_note', 'diagnosis', 'procedure', 'lab_order', 'imaging_order']);
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->json('data_json')->nullable();
            $table->foreignId('authored_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('icd10_code')->nullable();
            $table->string('cpt_code')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('medical_records', function (Blueprint $table) {
            $table->index(['organization_id', 'visit_id', 'record_type'], 'medrecs_org_visit_type_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
