<?php

namespace App\Services\Pdf;

use App\Models\MedicalRecord;
use App\Models\Patient;

class ContextBuilder
{
    public function buildFromRecord(MedicalRecord $record): array
    {
        $patient = $record->patient;

        return [
            'organization' => $this->formatOrganization($record->organization),
            'patient' => $this->formatPatient($patient),
            'visit' => $this->formatVisit($record->visit),
            'record' => $record->toArray(),
            'authored_by' => $this->formatAuthor($record->author),
            'now' => now()->toDateTimeString(),
            'date' => now()->toDateString(),
            'uuid' => $record->uuid,
        ];
    }

    public function formatPatient(?Patient $patient): array
    {
        if (!$patient) {
            return [];
        }

        return [
            'id' => $patient->id,
            'uuid' => $patient->uuid,
            'mrn' => $patient->mrn,
            'full_name' => $patient->full_name,
            'first_name' => $patient->first_name,
            'last_name' => $patient->last_name,
            'sex' => $patient->sex,
            'date_of_birth' => $patient->date_of_birth?->toDateString(),
            'age' => $patient->age,
            'national_id' => $patient->national_id,
            'email' => $patient->email,
            'phone' => $patient->phone,
            'address' => $patient->address,
            'emergency_contact' => $patient->emergency_contact,
            'notes' => $patient->notes,
            'is_active' => $patient->is_active,
        ];
    }

    private function formatOrganization($organization): array
    {
        if (!$organization) {
            return [];
        }

        return [
            'name' => $organization->name,
            'code' => $organization->code,
            'address' => $organization->address,
            'phone' => $organization->phone,
            'email' => $organization->email,
        ];
    }

    private function formatVisit($visit): array
    {
        if (!$visit) {
            return [];
        }

        return [
            'date' => $visit->date?->toDateString(),
            'type' => $visit->type,
            'diagnosis' => $visit->diagnosis,
            'notes' => $visit->notes,
            'status' => $visit->status,
        ];
    }

    private function formatAuthor($author): array
    {
        if (!$author) {
            return ['name' => 'Unknown'];
        }

        return [
            'name' => $author->name,
            'role' => $author->role,
            'email' => $author->email,
        ];
    }
}
