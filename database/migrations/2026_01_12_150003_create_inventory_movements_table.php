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
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->constrained()->onDelete('restrict');
            $table->foreignId('drug_id')->constrained()->onDelete('restrict');
            $table->enum('movement_type', ['receipt', 'adjustment', 'dispense']);
            $table->foreignId('related_prescription_item_id')->nullable()->constrained('prescription_items')->onDelete('set null');
            $table->integer('quantity');
            $table->text('reason')->nullable();
            $table->string('reference')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('performed_at')->useCurrent();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->index(['organization_id', 'drug_id', 'movement_type'], 'inv_movements_org_drug_type_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
