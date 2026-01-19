<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared("
            CREATE OR REPLACE FUNCTION enforce_rls_for_table(table_name text)
            RETURNS void AS $$
            BEGIN
                -- Enable RLS
                EXECUTE format('ALTER TABLE %I ENABLE ROW LEVEL SECURITY;', table_name);

                -- Drop existing policies if they exist
                EXECUTE format('DROP POLICY IF EXISTS %I_org_select ON %I;', table_name, table_name);
                EXECUTE format('DROP POLICY IF EXISTS %I_org_insert ON %I;', table_name, table_name);
                EXECUTE format('DROP POLICY IF EXISTS %I_org_update ON %I;', table_name, table_name);

                -- Create SELECT policy
                EXECUTE format(
                    'CREATE POLICY %I_org_select ON %I FOR SELECT USING (organization_id = current_setting(''app.current_organization_id'')::int);',
                    table_name, table_name
                );

                -- Create INSERT policy
                EXECUTE format(
                    'CREATE POLICY %I_org_insert ON %I FOR INSERT WITH CHECK (organization_id = current_setting(''app.current_organization_id'')::int);',
                    table_name, table_name
                );

                -- Create UPDATE policy
                EXECUTE format(
                    'CREATE POLICY %I_org_update ON %I FOR UPDATE USING (organization_id = current_setting(''app.current_organization_id'')::int) WITH CHECK (organization_id = current_setting(''app.current_organization_id'')::int);',
                    table_name, table_name
                );
            END;
            $$ LANGUAGE plpgsql;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP FUNCTION IF EXISTS enforce_rls_for_table(text)");
    }
};
