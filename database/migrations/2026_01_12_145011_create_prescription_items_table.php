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
        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('prescription_id')->constrained()->onDelete('cascade');
            $table->string('drug_code');
            $table->string('drug_name');
            $table->string('dosage');
            $table->string('route')->nullable();
            $table->string('frequency')->nullable();
            $table->integer('duration_days')->nullable();
            $table->integer('quantity')->default(0);
            $table->text('instructions')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('prescription_items', function (Blueprint $table) {
            $table->index(['prescription_id', 'drug_code'], 'presc_items_presc_drug_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescription_items');
    }
};
