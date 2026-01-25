<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Organization;
use Illuminate\Support\Str;

class CreateOrganization extends Command
{
    protected $signature = 'org:create
                            {name : Organization name}
                            {--code= : Organization short code (optional)}';

    protected $description = 'Create a new organization (clinic)';

    public function handle(): int
    {
        $name = $this->argument('name');
        $code = $this->option('code');

        // Auto-generate code if not provided
        if (! $code) {
            $code = strtoupper(Str::random(3));
        }

        // Normalize code (recommended)
        $code = strtolower($code);

        // Ensure code uniqueness
        if (Organization::where('code', $code)->exists()) {
            $this->error("Organization code '{$code}' already exists.");
            return Command::FAILURE;
        }

        // ---- SLUG GENERATION ----
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 2;

        while (Organization::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }
        // -------------------------

        $organization = Organization::create([
            'name' => $name,
            'code' => $code,
            'slug' => $slug,
            'is_active' => true,
        ]);

        $this->info('Organization created successfully 🎉');
        $this->table(
            ['ID', 'UUID', 'Name', 'Code', 'Slug', 'Active'],
            [[
                $organization->id,
                $organization->uuid ?? '—',
                $organization->name,
                $organization->code,
                $organization->slug,
                $organization->is_active ? 'Yes' : 'No',
            ]]
        );

        return Command::SUCCESS;
    }
}
