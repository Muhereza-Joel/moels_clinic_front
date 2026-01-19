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
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->constrained()->onDelete('restrict');
            $table->foreignId('patient_id')->constrained()->onDelete('restrict');
            $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('doctor_id')->constrained()->onDelete('restrict');
            $table->timestamp('visit_date')->useCurrent();
            $table->enum('status', ['open', 'finalized', 'cancelled']);
            $table->text('chief_complaint')->nullable();
            $table->json('triage_json')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->index(['organization_id', 'patient_id', 'visit_date'], 'visits_org_patient_date_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
