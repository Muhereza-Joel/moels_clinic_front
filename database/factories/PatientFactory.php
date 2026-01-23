<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;
use Ramsey\Uuid\Uuid;

class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        return [
            'uuid' => Uuid::uuid4()->toString(),
            'organization_id' => 2, // fixed org ID for your case
            'mrn' => null, // will be auto-generated in Patient::booted()
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'sex' => $this->faker->randomElement(['Male', 'Female']),
            'date_of_birth' => $this->faker->date(),
            'national_id' => strtoupper($this->faker->bothify('??########')),
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'emergency_contact' => [
                'name' => $this->faker->name,
                'phone' => $this->faker->phoneNumber,
            ],
            'notes' => $this->faker->sentence,
            'is_active' => true,
        ];
    }
}
