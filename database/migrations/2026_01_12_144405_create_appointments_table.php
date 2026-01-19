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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->constrained()->onDelete('restrict');
            $table->foreignId('patient_id')->constrained()->onDelete('restrict');
            $table->foreignId('doctor_id')->constrained()->onDelete('restrict');
            $table->foreignId('room_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamp('scheduled_start');
            $table->timestamp('scheduled_end');
            $table->enum('status', ['pending', 'confirmed', 'checked_in', 'completed', 'cancelled', 'no_show']);
            $table->text('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->index(['organization_id', 'doctor_id', 'scheduled_start'], 'appointments_org_doctor_start_index');
            $table->index(['organization_id', 'patient_id', 'scheduled_start'], 'appointments_org_patient_start_index');
            $table->index(['organization_id', 'status'], 'appointments_org_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
