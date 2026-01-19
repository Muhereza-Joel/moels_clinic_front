<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Organization;
use Illuminate\Support\Str;

class CreateOrganization extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'org:create
                            {name : Organization name}
                            {--code= : Organization short code (optional)}';

    /**
     * The console command description.
     */
    protected $description = 'Create a new organization (clinic)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->argument('name');
        $code = $this->option('code');

        // Auto-generate code if not provided
        if (!$code) {
            $code = strtoupper(Str::random(3));
        }

        // Ensure code uniqueness
        if (Organization::where('code', $code)->exists()) {
            $this->error("Organization code '{$code}' already exists.");
            return Command::FAILURE;
        }

        $organization = Organization::create([
            'name' => $name,
            'code' => $code,
            'is_active' => true,
        ]);

        $this->info('Organization created successfully 🎉');
        $this->table(
            ['ID', 'UUID', 'Name', 'Code', 'Active'],
            [[
                $organization->id,
                $organization->uuid ?? '—',
                $organization->name,
                $organization->code,
                $organization->is_active ? 'Yes' : 'No',
            ]]
        );

        return Command::SUCCESS;
    }
}
