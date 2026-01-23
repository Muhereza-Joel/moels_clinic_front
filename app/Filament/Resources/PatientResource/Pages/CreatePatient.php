<?php

namespace App\Filament\Resources\PatientResource\Pages;

use App\Filament\Resources\PatientResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePatient extends CreateRecord
{
    protected static string $resource = PatientResource::class;

    /**
     * Pre-fill the form when the page loads.
     */
    protected function fillForm(): void
    {
        $this->callHook('beforeFill');

        $data = [];

        $queryParams = request()->query();

        if (!empty($queryParams)) {
            foreach ($queryParams as $key => $value) {
                if ($key === 'sex') {
                    // Normalize case to match options
                    $value = ucfirst(strtolower($value));
                }

                if ($key === 'date_of_birth') {
                    // Ensure proper format for DatePicker
                    try {
                        $value = \Carbon\Carbon::parse($value)->format('Y-m-d');
                    } catch (\Exception $e) {
                        // ignore invalid date
                    }
                }

                $data[$key] = $value;
            }
        }

        $this->form->fill($data);

        $this->callHook('afterFill');
    }
}
