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
        Schema::create('lab_results', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('lab_order_id')->constrained()->onDelete('cascade');
            $table->string('analyte_code');
            $table->string('value_text')->nullable();
            $table->decimal('value_numeric', 12, 4)->nullable();
            $table->string('unit')->nullable();
            $table->string('reference_range')->nullable();
            $table->boolean('flagged')->default(false);
            $table->timestamp('result_date')->useCurrent();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('lab_results', function (Blueprint $table) {
            $table->index(['lab_order_id', 'analyte_code'], 'lab_results_order_analyte_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_results');
    }
};
