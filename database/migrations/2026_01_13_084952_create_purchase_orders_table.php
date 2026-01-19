<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');

            // Order Information
            $table->string('order_number')->unique();
            $table->foreignId('supplier_id')->constrained()->onDelete('restrict');
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->date('actual_delivery_date')->nullable();

            // Status Tracking
            $table->enum('status', [
                'draft',
                'pending_approval',
                'approved',
                'ordered',
                'partially_received',
                'fully_received',
                'cancelled',
                'closed'
            ])->default('draft');

            $table->enum('payment_status', [
                'pending',
                'partial',
                'paid',
                'overdue'
            ])->default('pending');

            $table->enum('delivery_status', [
                'pending',
                'processing',
                'shipped',
                'partially_delivered',
                'delivered',
                'on_time',
                'delayed',
                'cancelled'
            ])->default('pending');

            // Order Details
            $table->integer('total_items')->default(0);
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('shipping_cost', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->decimal('amount_paid', 14, 2)->default(0);
            $table->decimal('amount_due', 14, 2)->default(0);

            // Payment Information
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->date('payment_due_date')->nullable();
            $table->date('payment_date')->nullable();

            // Shipping Information
            $table->string('shipping_method')->nullable();
            $table->string('tracking_number')->nullable();
            $table->text('shipping_address')->nullable();

            // Quality Control
            $table->boolean('has_quality_issues')->default(false);
            $table->text('quality_notes')->nullable();
            $table->integer('rejected_items_count')->default(0);
            $table->decimal('rejected_items_value', 14, 2)->default(0);

            // Lead Time Tracking
            $table->integer('estimated_lead_time_days')->nullable();
            $table->integer('actual_lead_time_days')->nullable();

            // Approval Workflow
            $table->foreignId('requested_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('approved_at')->nullable();
            $table->text('approval_notes')->nullable();

            // Receiving Information
            $table->foreignId('received_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('received_at')->nullable();
            $table->text('receiving_notes')->nullable();

            // Notes & Attachments
            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->json('attachments')->nullable(); // Store file paths

            // Audit
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // Computed columns for performance
            $table->integer('days_overdue')->nullable();
            $table->integer('delivery_delay_days')->nullable();
        });

        // Indexes for performance
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->index(['organization_id', 'order_number']);
            $table->index(['organization_id', 'supplier_id']);
            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'payment_status']);
            $table->index(['organization_id', 'order_date']);
            $table->index(['organization_id', 'expected_delivery_date']);
            $table->index(['organization_id', 'payment_due_date']);
            $table->index(['organization_id', 'created_by']);
            $table->index(['supplier_id', 'status']);
            $table->index(['status', 'payment_status']);
            $table->fullText(['order_number', 'notes', 'internal_notes'], 'purchase_orders_search_fulltext');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
