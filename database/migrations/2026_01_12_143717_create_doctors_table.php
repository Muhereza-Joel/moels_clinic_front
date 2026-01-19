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
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->constrained()->onDelete('restrict');
            $table->foreignId('user_id')->unique()->constrained()->onDelete('restrict');
            $table->string('specialty');
            $table->string('license_number')->unique();
            $table->json('working_hours_json')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('doctors', function (Blueprint $table) {
            $table->index(['organization_id', 'specialty'], 'doctors_org_specialty_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
