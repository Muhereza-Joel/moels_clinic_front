<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CreateUser extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'user:create';

    /**
     * The console command description.
     */
    protected $description = 'Create a new user and assign them to an organization';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Ensure organizations exist
        $organizations = Organization::orderBy('name')->get();

        if ($organizations->isEmpty()) {
            $this->error('No organizations found. Create an organization first.');
            return Command::FAILURE;
        }

        // Select organization
        $selected = $this->choice(
            'Select organization',
            $organizations->map(fn($org) => "{$org->name} ({$org->code})")->toArray()
        );

        $organization = $organizations->first(
            fn($org) =>
            "{$org->name} ({$org->code})" === $selected
        );

        // User details
        $fullName = $this->ask('Full name');
        $email    = $this->ask('Email address');
        $password = $this->secret('Password');

        if (User::where('email', $email)->exists()) {
            $this->error('A user with this email already exists.');
            return Command::FAILURE;
        }

        // Create user
        $user = User::create([
            'organization_id' => $organization->id,
            'name'       => $fullName,
            'email'           => $email,
            'password'        => Hash::make($password),
            'is_active'       => true,
        ]);


        $this->info('User created successfully 🎉');
        $this->table(
            ['ID', 'UUID', 'Name', 'Email', 'Organization'],
            [[
                $user->id,
                $user->uuid,
                $user->name,
                $user->email,
                $organization->name,
            ]]
        );

        return Command::SUCCESS;
    }
}
