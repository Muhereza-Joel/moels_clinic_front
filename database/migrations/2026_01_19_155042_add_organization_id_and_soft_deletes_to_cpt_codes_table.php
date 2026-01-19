<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cpt_codes', function (Blueprint $table) {
            $table->foreignId('organization_id')
                ->nullable()
                ->constrained()
                ->onDelete('cascade')
                ->after('id');
            $table->timestamps();
            $table->softDeletes()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('cpt_codes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organization_id');
            $table->dropTimestamps();
            $table->dropSoftDeletes();
        });
    }
};
