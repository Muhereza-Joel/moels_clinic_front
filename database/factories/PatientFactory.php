<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;
use Ramsey\Uuid\Uuid;

class PatientFactory extends Factory
{
    protected $model = Patient::class;

    /**
     * Generate MRN with fixed prefix "MOELS-PT"
     */
    public static function generateMrn(): string
    {
        $orgCode = 'MOELS'; // fixed org prefix
        $patientPrefix = 'PT-';
        $suffixLength = 6;

        $max = (36 ** $suffixLength) - 1;

        do {
            $rand = random_int(0, $max);
            $base36 = base_convert($rand, 10, 36);

            $suffix = strtoupper(
                str_pad($base36, $suffixLength, '0', STR_PAD_LEFT)
            );

            $mrn = "{$orgCode}-{$patientPrefix}{$suffix}";
        } while (Patient::where('mrn', $mrn)->exists());

        return $mrn;
    }

    public function definition(): array
    {
        return [
            'uuid' => Uuid::uuid4()->toString(),
            'organization_id' => 2, // fixed org ID for your case
            'mrn' => self::generateMrn(), // call factory’s own MRN generator
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'sex' => $this->faker->randomElement(['male', 'female']),
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
