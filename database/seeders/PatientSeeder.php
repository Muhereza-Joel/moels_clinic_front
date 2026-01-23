<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Patient;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        // Generate 10,000 patients for organization_id = 2
        Patient::factory()->count(10000)->create();
    }
}
