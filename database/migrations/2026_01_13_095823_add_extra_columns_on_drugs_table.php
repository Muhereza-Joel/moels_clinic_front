<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drugs', function (Blueprint $table) {
            // Add new columns in logical groups

            // Identification
            $table->string('generic_name')->nullable()->after('name');
            $table->string('brand_name')->nullable()->after('generic_name');
            $table->string('manufacturer')->nullable()->after('brand_name');

            // Classification
            $table->foreignId('category_id')->nullable()->after('manufacturer')->constrained('drug_categories')->onDelete('set null');
            $table->foreignId('subcategory_id')->nullable()->after('category_id')->constrained('drug_categories')->onDelete('set null');
            $table->string('therapeutic_class')->nullable()->after('subcategory_id');
            $table->string('pharmacologic_class')->nullable()->after('therapeutic_class');

            // Pharmaceutical Details
            $table->string('unit_of_measure')->default('each')->after('strength');
            $table->integer('units_per_pack')->default(1)->after('unit_of_measure');

            // Inventory Details
            $table->integer('reorder_quantity')->nullable()->after('reorder_level');
            $table->integer('maximum_stock')->nullable()->after('reorder_quantity');
            $table->decimal('cost_price', 14, 2)->nullable()->after('unit_price');
            $table->decimal('selling_price', 14, 2)->nullable()->after('cost_price');
            $table->decimal('wholesale_price', 14, 2)->nullable()->after('selling_price');

            // Batch/Expiry Tracking
            $table->string('batch_number')->nullable()->after('wholesale_price');
            $table->date('expiry_date')->nullable()->after('batch_number');
            $table->date('manufacture_date')->nullable()->after('expiry_date');

            // Storage & Handling
            $table->string('storage_condition')->nullable()->after('manufacture_date');
            $table->string('storage_location')->nullable()->after('storage_condition');
            $table->text('storage_instructions')->nullable()->after('storage_location');

            // Regulatory & Safety
            $table->string('regulatory_number')->nullable()->after('storage_instructions');
            $table->boolean('requires_prescription')->default(true)->after('regulatory_number');
            $table->boolean('is_controlled_substance')->default(false)->after('requires_prescription');
            $table->string('controlled_schedule')->nullable()->after('is_controlled_substance');
            $table->boolean('is_dangerous_drug')->default(false)->after('controlled_schedule');

            // Clinical Information
            $table->text('indications')->nullable()->after('is_dangerous_drug');
            $table->text('contraindications')->nullable()->after('indications');
            $table->text('side_effects')->nullable()->after('contraindications');
            $table->text('dosage_instructions')->nullable()->after('side_effects');
            $table->text('administration_route')->nullable()->after('dosage_instructions');
            $table->text('special_precautions')->nullable()->after('administration_route');

            // Supplier Information
            $table->foreignId('primary_supplier_id')->nullable()->after('special_precautions')->constrained('suppliers')->onDelete('set null');
            $table->foreignId('secondary_supplier_id')->nullable()->after('primary_supplier_id')->constrained('suppliers')->onDelete('set null');
            $table->string('supplier_code')->nullable()->after('secondary_supplier_id');
            $table->integer('lead_time_days')->nullable()->after('supplier_code');

            // Usage Tracking
            $table->integer('minimum_order_quantity')->nullable()->after('lead_time_days');
            $table->integer('maximum_order_quantity')->nullable()->after('minimum_order_quantity');
            $table->integer('monthly_usage')->default(0)->after('maximum_order_quantity');
            $table->date('last_purchase_date')->nullable()->after('monthly_usage');
            $table->date('last_dispensed_date')->nullable()->after('last_purchase_date');

            // Status & Flags
            $table->boolean('is_discontinued')->default(false)->after('is_active');
            $table->date('discontinued_date')->nullable()->after('is_discontinued');
            $table->string('discontinued_reason')->nullable()->after('discontinued_date');
            $table->boolean('is_branded')->default(false)->after('discontinued_reason');
            $table->boolean('is_generic')->default(true)->after('is_branded');

            $table->text('notes')->nullable()->after('updated_by');
            $table->json('alternative_names')->nullable()->after('notes');
        });

        // Add new indexes
        Schema::table('drugs', function (Blueprint $table) {
            $table->index(['organization_id', 'generic_name'], 'drugs_org_generic_name_index');
            $table->index(['organization_id', 'category_id'], 'drugs_org_category_index');
            $table->index(['expiry_date'], 'drugs_expiry_date_index');
            $table->index(['is_active', 'stock_quantity'], 'drugs_active_stock_index');
            $table->index(['requires_prescription'], 'drugs_prescription_index');

            $table->index(['name', 'generic_name', 'brand_name'], 'drugs_search_fulltext');
        });
    }

    public function down(): void
    {
        Schema::table('drugs', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex('drugs_org_generic_name_index');
            $table->dropIndex('drugs_org_category_index');
            $table->dropIndex('drugs_expiry_date_index');
            $table->dropIndex('drugs_active_stock_index');
            $table->dropIndex('drugs_prescription_index');
            $table->dropIndex('drugs_search_fulltext');

            // Drop foreign keys
            $table->dropForeign(['category_id']);
            $table->dropForeign(['subcategory_id']);
            $table->dropForeign(['primary_supplier_id']);
            $table->dropForeign(['secondary_supplier_id']);

            // Drop columns in reverse order (newest first)
            $table->dropColumn('alternative_names');
            $table->dropColumn('notes');
            $table->dropColumn('is_generic');
            $table->dropColumn('is_branded');
            $table->dropColumn('discontinued_reason');
            $table->dropColumn('discontinued_date');
            $table->dropColumn('is_discontinued');
            $table->dropColumn('last_dispensed_date');
            $table->dropColumn('last_purchase_date');
            $table->dropColumn('monthly_usage');
            $table->dropColumn('maximum_order_quantity');
            $table->dropColumn('minimum_order_quantity');
            $table->dropColumn('lead_time_days');
            $table->dropColumn('supplier_code');
            $table->dropColumn('secondary_supplier_id');
            $table->dropColumn('primary_supplier_id');
            $table->dropColumn('special_precautions');
            $table->dropColumn('administration_route');
            $table->dropColumn('dosage_instructions');
            $table->dropColumn('side_effects');
            $table->dropColumn('contraindications');
            $table->dropColumn('indications');
            $table->dropColumn('is_dangerous_drug');
            $table->dropColumn('controlled_schedule');
            $table->dropColumn('is_controlled_substance');
            $table->dropColumn('requires_prescription');
            $table->dropColumn('regulatory_number');
            $table->dropColumn('storage_instructions');
            $table->dropColumn('storage_location');
            $table->dropColumn('storage_condition');
            $table->dropColumn('manufacture_date');
            $table->dropColumn('expiry_date');
            $table->dropColumn('batch_number');
            $table->dropColumn('wholesale_price');
            $table->dropColumn('selling_price');
            $table->dropColumn('cost_price');
            $table->dropColumn('maximum_stock');
            $table->dropColumn('reorder_quantity');
            $table->dropColumn('units_per_pack');
            $table->dropColumn('unit_of_measure');
            $table->dropColumn('pharmacologic_class');
            $table->dropColumn('therapeutic_class');
            $table->dropColumn('subcategory_id');
            $table->dropColumn('category_id');
            $table->dropColumn('manufacturer');
            $table->dropColumn('brand_name');
            $table->dropColumn('generic_name');
        });
    }
};
