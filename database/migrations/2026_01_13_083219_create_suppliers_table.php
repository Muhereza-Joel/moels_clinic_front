<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');

            // Basic Information
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('alternative_phone')->nullable();

            // Address
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();

            // Business Details
            $table->string('tax_id')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('payment_terms')->nullable();
            $table->decimal('credit_limit', 14, 2)->nullable();
            $table->integer('payment_days')->default(30);

            // Banking Details
            $table->string('bank_name')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_branch')->nullable();

            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_preferred')->default(false);
            $table->enum('rating', ['excellent', 'good', 'average', 'poor'])->nullable();

            // Additional Info
            $table->text('notes')->nullable();
            $table->string('website')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->index(['organization_id', 'name']);
            $table->index(['organization_id', 'is_active']);
            $table->unique(['organization_id', 'code']);
            $table->index(['organization_id', 'is_preferred']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
