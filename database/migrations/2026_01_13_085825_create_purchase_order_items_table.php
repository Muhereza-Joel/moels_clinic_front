<?php
// database/migrations/create_purchase_order_items_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');
            $table->string('item_type');
            $table->foreignId('drug_id')
                ->nullable()
                ->constrained()
                ->onDelete('restrict');
            $table->string('drug_code')->nullable();
            $table->string('drug_name')->nullable();
            $table->string('item_name')->nullable();
            $table->string('strength')->nullable();
            $table->string('form')->nullable();
            $table->integer('quantity');
            $table->integer('quantity_received')->default(0);
            $table->decimal('unit_price', 14, 2);
            $table->decimal('total_price', 14, 2);
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['purchase_order_id', 'drug_id']);
            $table->index('drug_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};
