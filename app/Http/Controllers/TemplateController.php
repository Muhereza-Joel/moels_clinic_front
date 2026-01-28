<?php

namespace App\Http\Controllers;

use App\Models\MedicalRecord;
use App\Models\PdfTemplate;
use App\Models\Organization;
use App\Models\Patient;
use App\Models\Record;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class TemplateController extends Controller
{
    /**
     * Display a listing of the templates.
     */
    public function index()
    {
        $user = Auth::user();

        $templates = PdfTemplate::where('organization_id', $user->organization_id)
            ->with('organization')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($template) {
                return [
                    'id' => $template->id,
                    'uuid' => $template->uuid,
                    'code' => $template->code,
                    'name' => $template->name,
                    'version' => $template->version,
                    'active' => $template->active,
                    'created_at' => $template->created_at->format('Y-m-d H:i'),
                    'updated_at' => $template->updated_at->format('Y-m-d H:i'),
                    'organization' => $template->organization->name,
                    'layout' => $template->layout, // Include layout for preview
                ];
            });

        return Inertia::render('Templates/Index', [
            'templates' => $templates,
        ]);
    }

    /**
     * Show the form for creating a new template.
     */
    public function create()
    {
        $user = Auth::user();
        $organization = $user->organization;

        // Generate a unique code for the template
        $code = 'TMPL-' . strtoupper(Str::random(6));

        // Get available records for preview
        $records = MedicalRecord::where('organization_id', $user->organization_id)
            ->with('patient')
            ->latest()
            ->limit(50)
            ->get()
            ->map(function ($record) {
                return [
                    'id' => $record->id,
                    'uuid' => $record->uuid,
                    'patient' => $record->patient ? [
                        'name' => $record->patient->full_name,
                        'mrn' => $record->patient->mrn,
                    ] : null,
                    'created_at' => $record->created_at->format('Y-m-d'),
                ];
            });

        return Inertia::render('Templates/Create', [
            'code' => $code,
            'records' => $records,
            'defaultLayout' => $this->getDefaultLayout(),
        ]);
    }

    /**
     * Store a newly created template in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Wrap frontend data into layout
        $layout = array_merge(
            $this->getDefaultLayout(), // default orientation, page_size, margins
            $request->input('layout', []), // in case frontend sends layout
            [
                'sections' => $request->input('sections', []),
                'footer' => $request->input('footer', $this->getDefaultLayout()['footer']),
            ]
        );

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:pdf_templates,code',
            'description' => 'nullable|string',
            'layout' => 'required|array',
            'layout.orientation' => 'required|in:portrait,landscape',
            'layout.page_size' => 'required|in:A4,A3,Letter,Legal',
            'layout.margins' => 'required|array',
            'layout.margins.top' => 'required|numeric|min:0|max:100',
            'layout.margins.right' => 'required|numeric|min:0|max:100',
            'layout.margins.bottom' => 'required|numeric|min:0|max:100',
            'layout.margins.left' => 'required|numeric|min:0|max:100',
            'layout.sections' => 'nullable|array',
            'layout.footer' => 'nullable|array',
            'layout.styles' => 'nullable|array',
            'version' => 'nullable|string|max:20',
            'active' => 'boolean',
            'mock_schema' => 'nullable|array',
        ]);

        try {
            $template = PdfTemplate::create([
                'uuid' => Uuid::uuid4()->toString(),
                'organization_id' => $user->organization_id,
                'code' => $validated['code'],
                'name' => $validated['name'],
                'layout' => $layout,
                'version' => $validated['version'] ?? '1.0.0',
                'active' => $validated['active'] ?? true,
            ]);

            // Add mock_schema if provided
            if ($request->has('mock_schema')) {
                $layout['mock_schema'] = $request->input('mock_schema');
                $template->update(['layout' => $layout]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Template created!',
                'uuid' => $template->uuid,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create template: ' . $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Display the template builder UI.
     */
    public function builder(PdfTemplate $template)
    {
        $user = Auth::user();

        // Verify template belongs to user's organization
        if ($template->organization_id !== $user->organization_id) {
            abort(403, 'Unauthorized access to template.');
        }

        // Get records for preview
        $records = MedicalRecord::where('organization_id', $user->organization_id)
            ->with('patient')
            ->latest()
            ->limit(100)
            ->get()
            ->map(function ($record) {
                return [
                    'id' => $record->id,
                    'uuid' => $record->uuid,
                    'patient' => $record->patient ? [
                        'name' => $record->patient->full_name,
                        'mrn' => $record->patient->mrn,
                        'dob' => $record->patient->date_of_birth?->format('Y-m-d'),
                        'phone' => $record->patient->phone,
                    ] : null,
                    'created_at' => $record->created_at->format('Y-m-d H:i'),
                ];
            });

        // Get sample data for preview
        $sampleData = $this->getSampleData($user);

        return Inertia::render('Templates/Builder', [
            'template' => [
                'id' => $template->id,
                'uuid' => $template->uuid,
                'code' => $template->code,
                'name' => $template->name,
                'version' => $template->version,
                'active' => $template->active,
                'layout' => $template->layout ?? $this->getDefaultLayout(),
            ],
            'records' => $records,
            'sampleData' => $sampleData,
        ]);
    }

    /**
     * Display the specified template.
     */
    public function show(PdfTemplate $template)
    {
        $user = Auth::user();

        if ($template->organization_id !== $user->organization_id) {
            abort(403);
        }

        return Inertia::render('Templates/Show', [
            'template' => $template,
            'layout' => $template->getResolvedLayout(),
        ]);
    }

    /**
     * Show the form for editing the template.
     */
    public function edit(PdfTemplate $template)
    {
        $user = Auth::user();

        if ($template->organization_id !== $user->organization_id) {
            abort(403);
        }

        $records = MedicalRecord::where('organization_id', $user->organization_id)
            ->with('patient')
            ->latest()
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Template updated successfully',
            'template' => $template
        ]);
    }

    /**
     * Update the template in storage.
     */
    public function update(Request $request, PdfTemplate $template)
    {
        $user = Auth::user();

        if ($template->organization_id !== $user->organization_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:50|unique:pdf_templates,code,' . $template->id,
            'layout' => 'sometimes|array',
            'layout.orientation' => 'sometimes|required|in:portrait,landscape',
            'layout.page_size' => 'sometimes|required|in:A4,A3,Letter,Legal',
            'layout.margins' => 'sometimes|array',
            'layout.sections' => 'nullable|array',
            'layout.footer' => 'nullable|array',
            'layout.styles' => 'nullable|array',
            'version' => 'sometimes|string|max:20',
            'active' => 'sometimes|boolean',
            'mock_schema' => 'nullable|array',
        ]);

        try {
            // Update layout if provided
            if (isset($validated['layout'])) {
                $template->update(['layout' => $validated['layout']]);
            }

            // Update other fields
            $updatable = ['name', 'code', 'version', 'active'];
            $updateData = [];
            foreach ($updatable as $field) {
                if (isset($validated[$field])) {
                    $updateData[$field] = $validated[$field];
                }
            }

            if (!empty($updateData)) {
                $template->update($updateData);
            }

            // Update mock schema if provided
            if (isset($validated['mock_schema'])) {
                $layout = $template->layout ?? [];
                $layout['mock_schema'] = $validated['mock_schema'];
                $template->update(['layout' => $layout]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Template updated successfully',
                'template' => $template,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update template: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clone a template.
     */
    public function clone(Request $request, PdfTemplate $template)
    {
        $user = Auth::user();

        if ($template->organization_id !== $user->organization_id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:pdf_templates,code',
        ]);

        try {
            DB::beginTransaction();

            $newTemplate = PdfTemplate::create([
                'uuid' => Str::uuid(),
                'organization_id' => $user->organization_id,
                'code' => $request->code,
                'name' => $request->name,
                'layout' => $template->layout,
                'version' => '1.0.0',
                'active' => false,
            ]);

            DB::commit();

            return redirect()->route('templates.builder', $newTemplate->uuid)
                ->with('success', 'Template cloned successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to clone template: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified template from storage.
     */
    public function destroy(PdfTemplate $template)
    {
        $user = Auth::user();

        if ($template->organization_id !== $user->organization_id) {
            abort(403);
        }

        try {
            $template->delete();
            return response()->json([
                'success' => true,
                'message' => 'Template deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete template: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get default layout structure.
     */
    private function getDefaultLayout(): array
    {
        return [
            'orientation' => 'portrait',
            'page_size' => 'A4',
            'margins' => [
                'top' => 20,
                'right' => 15,
                'bottom' => 20,
                'left' => 15,
            ],
            'sections' => [],
            'footer' => [
                'text' => 'Generated on {{now}}',
                'enabled' => true,
                'align' => 'center',
                'font_size' => 10,
            ],
            'styles' => [
                'font_family' => 'Helvetica',
                'font_size' => 12,
                'line_height' => 1.5,
                'header_color' => '#1a56db',
                'text_color' => '#111827',
                'border_color' => '#d1d5db',
            ],
        ];
    }

    /**
     * Get sample data for preview.
     */
    private function getSampleData($user): array
    {
        return [
            'organization' => [
                'name' => $user->organization->name,
                'code' => $user->organization->code,
                'address' => $user->organization->address ?? '123 Medical Center Dr',
                'phone' => $user->organization->phone ?? '+1 234 567 8900',
                'email' => $user->organization->email ?? 'info@clinic.example',
            ],
            'patient' => [
                'full_name' => 'John A. Doe',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'mrn' => 'KLA-PT-0F3A9K',
                'date_of_birth' => '1990-01-01',
                'age' => 34,
                'sex' => 'Male',
                'phone' => '+1 234 567 8901',
                'email' => 'john.doe@example.com',
                'address' => '456 Oak Street, Springfield',
                'emergency_contact' => 'Jane Doe (555-0123)',
                'is_active' => true,
            ],
            'visit' => [
                'date' => now()->format('Y-m-d'),
                'type' => 'General Checkup',
                'diagnosis' => 'Hypertension Stage 1',
                'notes' => 'Patient presented with elevated blood pressure. Recommended lifestyle changes and follow-up in 3 months.',
                'status' => 'Completed',
            ],
            'record' => [
                'id' => 12345,
                'uuid' => 'REC-' . Str::random(8),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            'authored_by' => [
                'name' => 'Dr. Sarah Johnson',
                'role' => 'General Practitioner',
                'license_number' => 'GP-789012',
            ],
            'now' => now()->format('Y-m-d H:i:s'),
            'date' => now()->format('Y-m-d'),
            'uuid' => 'TMP-' . Str::random(8),
        ];
    }

    /**
     * Export template as JSON.
     */
    public function export(PdfTemplate $template)
    {
        $user = Auth::user();

        if ($template->organization_id !== $user->organization_id) {
            abort(403);
        }

        $exportData = [
            'name' => $template->name,
            'code' => $template->code,
            'version' => $template->version,
            'layout' => $template->layout,
            'exported_at' => now()->toISOString(),
            'exported_by' => $user->email,
        ];

        return response()->json($exportData, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $template->code . '.json"',
        ]);
    }

    /**
     * Import template from JSON.
     */
    public function import(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'file' => 'required|file|mimes:json|max:2048',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:pdf_templates,code',
        ]);

        try {
            $json = file_get_contents($request->file('file')->getRealPath());
            $data = json_decode($json, true);

            if (!isset($data['layout'])) {
                throw new \Exception('Invalid template file: missing layout data');
            }

            $template = PdfTemplate::create([
                'uuid' => Str::uuid(),
                'organization_id' => $user->organization_id,
                'code' => $request->code,
                'name' => $request->name,
                'layout' => $data['layout'],
                'version' => $data['version'] ?? '1.0.0',
                'active' => false,
            ]);

            return redirect()->route('templates.builder', $template->uuid)
                ->with('success', 'Template imported successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to import template: ' . $e->getMessage()]);
        }
    }

    /**
     * Update just the layout of a template.
     */
    public function updateLayout(Request $request, PdfTemplate $template)
    {
        $user = Auth::user();

        if ($template->organization_id !== $user->organization_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'layout' => 'required|array',
            'layout.sections' => 'nullable|array',
            'layout.footer' => 'nullable|array',
            'layout.styles' => 'nullable|array',
            'mock_schema' => 'nullable|array',
        ]);

        try {
            $layout = $template->layout ?? [];

            // Merge updates with existing layout
            $updatedLayout = array_merge($layout, $validated['layout']);

            // Add mock schema if provided
            if (isset($validated['mock_schema'])) {
                $updatedLayout['mock_schema'] = $validated['mock_schema'];
            }

            $template->update(['layout' => $updatedLayout]);

            return response()->json([
                'success' => true,
                'message' => 'Layout updated successfully',
                'template' => $template->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update layout: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get template by UUID for preview.
     */
    public function getByUuid($uuid)
    {
        $template = PdfTemplate::where('uuid', $uuid)->firstOrFail();

        // Check if user has access
        if (Auth::check()) {
            $user = Auth::user();
            if ($template->organization_id !== $user->organization_id) {
                abort(403);
            }
        }

        return response()->json([
            'id' => $template->id,
            'uuid' => $template->uuid,
            'code' => $template->code,
            'name' => $template->name,
            'layout' => $template->getResolvedLayout(),
        ]);
    }
}
