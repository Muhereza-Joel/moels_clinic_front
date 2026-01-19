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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->enum('item_type', ['consultation', 'procedure', 'lab', 'drug', 'misc']);
            $table->text('description');
            $table->decimal('unit_price', 14, 2);
            $table->decimal('quantity', 14, 2);
            $table->decimal('total_amount', 14, 2);
            $table->json('metadata_json')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->index(['invoice_id', 'item_type'], 'inv_items_invoice_type_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
