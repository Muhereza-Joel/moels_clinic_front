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
        Schema::create('lab_orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->constrained()->onDelete('restrict');
            $table->foreignId('visit_id')->constrained()->onDelete('cascade');
            $table->foreignId('ordered_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('order_date')->useCurrent();
            $table->string('panel_code')->nullable();
            $table->enum('status', ['ordered', 'in_progress', 'completed', 'cancelled']);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_orders');
    }
};
