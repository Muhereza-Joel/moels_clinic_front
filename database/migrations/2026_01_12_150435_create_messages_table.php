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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->constrained()->onDelete('restrict');
            $table->foreignId('recipient_patient_id')->nullable()->constrained('patients')->onDelete('set null');
            $table->foreignId('recipient_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('channel', ['sms', 'whatsapp', 'email']);
            $table->enum('message_type', ['appointment_reminder', 'billing_notice', 'general']);
            $table->text('content');
            $table->enum('status', ['queued', 'sent', 'delivered', 'failed']);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('provider_ref')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->index(['organization_id', 'channel', 'status'], 'messages_org_channel_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
