<?php

namespace App\Services\Pdf;

use App\Models\MedicalRecord;

class ContextBuilder
{
    public function build(MedicalRecord $record): array
    {
        return [
            'organization' => $record->organization->toArray(),
            'patient' => $record->patient->toArray(),
            'visit' => $record->visit->toArray(),
            'record_type' => $record->recordType->toArray(),
            'icd10' => optional($record->icd10)->toArray(),
            'cpt' => optional($record->cpt)->toArray(),
            'authored_by' => optional($record->authoredBy)->toArray(),
            'content' => $record->content,
            'data_json' => $record->data_json,
            'now' => now()->toDateTimeString(),
        ];
    }
}
