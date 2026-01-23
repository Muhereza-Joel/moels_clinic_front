<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('drug_sales', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Organization context
            $table->foreignId('organization_id')
                ->constrained()
                ->cascadeOnDelete();

            // Drug reference
            $table->foreignId('drug_id')
                ->constrained('drugs')
                ->cascadeOnDelete();

            // Optional patient link
            $table->foreignId('patient_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Walk-in customer info
            $table->string('customer_name')->nullable();
            $table->string('customer_contact')->nullable();

            // Sale details
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_price', 12, 2);
            $table->timestamp('sale_date')->useCurrent();

            // Staff who processed sale
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Payment details
            $table->string('payment_method')->nullable(); // cash, card, insurance
            $table->string('payment_status')->default('pending'); // pending, paid, refunded
            $table->string('receipt_number')->nullable();

            // Notes
            $table->text('notes')->nullable();

            // Soft deletes & timestamps
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drug_sales');
    }
};
