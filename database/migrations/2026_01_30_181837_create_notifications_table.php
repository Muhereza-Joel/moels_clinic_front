<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->json('data');
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamps();
        });

        // Defensive: composite index only if it doesn't exist
        DB::statement('CREATE INDEX IF NOT EXISTS notifications_notifiable_type_notifiable_id_index 
                       ON notifications (notifiable_type, notifiable_id);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Defensive: drop the composite index if it exists
        DB::statement('DROP INDEX IF EXISTS notifications_notifiable_type_notifiable_id_index;');

        Schema::dropIfExists('notifications');
    }
};
