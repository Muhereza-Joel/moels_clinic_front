<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PdfTemplate;
use App\Services\Pdf\ContextBuilder;
use App\Services\Pdf\TemplateRenderer;

class PdfPreviewController extends Controller
{
    public function preview(Request $request, PdfTemplate $template)
    {
        // Build fake or sample context data for testing
        $context = [
            'organization' => ['name' => 'Demo Clinic', 'address' => '123 Main St', 'phone' => '555-1234'],
            'patient' => ['name' => 'John Doe', 'dob' => '1990-01-01', 'uuid' => 'PAT-001'],
            'visit' => ['date' => now()->toDateString()],
            'authored_by' => ['name' => 'Dr. Smith'],
            'data_json' => [
                'medication' => 'Amoxicillin',
                'dosage' => '500mg',
                'frequency' => '3x daily',
                'instructions' => 'Take after meals'
            ],
            'now' => now()->toDateTimeString(),
            'uuid' => $template->uuid ?? 'TEST-UUID'
        ];

        $pdf = (new TemplateRenderer())->renderTemplate($template, $context);

        return response($pdf->Output('', 'S'))
            ->header('Content-Type', 'application/pdf');
    }
}
