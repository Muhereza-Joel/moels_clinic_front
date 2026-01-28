<?php

namespace App\Http\Controllers;

use App\Models\MedicalRecord;
use Illuminate\Http\Request;
use App\Models\PdfTemplate;
use App\Services\Pdf\ContextBuilder;
use App\Services\Pdf\TemplateRenderer;

class PdfPreviewController extends Controller
{
    /**
     * Preview a PDF using either mock data, a real record, or sample data.
     */
    public function preview(Request $request, PdfTemplate $template)
    {
        $recordId = $request->input('record_id');
        $mockData = $request->input('mock_data');

        if ($mockData) {
            // Use mock data from frontend
            $context = $this->buildMockContext($mockData);
        } elseif ($recordId) {
            // Use actual record data
            $record = MedicalRecord::with(['patient', 'organization', 'visit'])->find($recordId);

            if ($record) {
                $context = (new ContextBuilder())->buildFromRecord($record);
            } else {
                // Fallback to sample if record not found
                $context = $this->buildSampleContext();
            }
        } else {
            // Fallback to sample data
            $context = $this->buildSampleContext();
        }

        $pdf = (new TemplateRenderer())->renderTemplate($template, $context);

        return response($pdf->Output('', 'S'))
            ->header('Content-Type', 'application/pdf');
    }

    /**
     * Preview a PDF using only mock data.
     */
    public function previewMock(Request $request, PdfTemplate $template)
    {
        $mockData = $request->input('mock_data', []);
        $context = $this->buildMockContext($mockData);

        $pdf = (new TemplateRenderer())->renderTemplate($template, $context);

        return response($pdf->Output('', 'S'))
            ->header('Content-Type', 'application/pdf');
    }

    /**
     * Build context from mock data.
     */
    private function buildMockContext(array $mockData): array
    {
        $defaultContext = [
            'organization' => [
                'name' => 'Demo Clinic',
                'address' => '123 Main St',
                'phone' => '555-1234',
                'code' => 'KLA',
            ],
            'authored_by' => [
                'name' => 'Dr. Smith',
                'role' => 'Physician',
            ],
            'now' => now()->toDateTimeString(),
            'date' => now()->toDateString(),
            'uuid' => 'MOCK-' . uniqid(),
        ];

        return array_merge($defaultContext, $mockData);
    }

    /**
     * Build a sample context for previewing PDFs.
     */
    private function buildSampleContext(): array
    {
        return [
            'organization' => [
                'name' => 'Demo Clinic',
                'address' => '123 Main St',
                'phone' => '555-1234',
            ],
            'patient' => [
                'full_name' => 'John Doe',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'date_of_birth' => '1990-01-01',
                'mrn' => 'KLA-PT-0F3A9K',
                'age' => 34,
                'phone' => '555-0101',
                'email' => 'john@example.com',
                'address' => '456 Oak St',
                'is_active' => true,
            ],
            'visit' => [
                'date' => now()->toDateString(),
                'diagnosis' => 'Routine Checkup',
                'notes' => 'Patient in good health.',
            ],
            'authored_by' => ['name' => 'Dr. Smith'],
            'data_json' => [
                'medication' => 'Amoxicillin',
                'dosage' => '500mg',
                'frequency' => '3x daily',
                'instructions' => 'Take after meals',
            ],
            'now' => now()->toDateTimeString(),
            'uuid' => 'TEST-UUID-' . uniqid(),
        ];
    }
}
