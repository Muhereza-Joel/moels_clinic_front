<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Find all tables in the public schema with organization_id column
        $tables = DB::table('information_schema.columns')
            ->select('table_name')
            ->where('table_schema', 'public')
            ->where('column_name', 'organization_id')
            ->pluck('table_name');

        foreach ($tables as $table) {
            DB::statement("SELECT enforce_rls_for_table('{$table}')");
        }
    }

    public function down(): void
    {
        $tables = DB::table('information_schema.columns')
            ->select('table_name')
            ->where('table_schema', 'public')
            ->where('column_name', 'organization_id')
            ->pluck('table_name');

        foreach ($tables as $table) {
            DB::statement("ALTER TABLE {$table} DISABLE ROW LEVEL SECURITY");
            DB::statement("DROP POLICY IF EXISTS {$table}_org_select ON {$table}");
            DB::statement("DROP POLICY IF EXISTS {$table}_org_insert ON {$table}");
            DB::statement("DROP POLICY IF EXISTS {$table}_org_update ON {$table}");
        }
    }
};
