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
        Schema::create('drugs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->constrained()->onDelete('restrict');
            $table->string('drug_code')->unique();
            $table->string('name');
            $table->string('form')->nullable();
            $table->string('strength')->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->integer('reorder_level')->default(0);
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('drugs', function (Blueprint $table) {
            $table->index(['organization_id', 'name'], 'drugs_org_name_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drugs');
    }
};
