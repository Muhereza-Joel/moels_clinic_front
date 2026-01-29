<?php

use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PdfPreviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TemplateController;
use App\Models\PdfTemplate;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect("clinika");
});
// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin'       => Route::has('login'),
//         'canRegister'    => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion'     => PHP_VERSION,
//     ]);
// });

/*
|--------------------------------------------------------------------------
| Authenticated & Verified Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/template-builder', function () {
        $organizationId = Auth::user()->organization_id ?? null;

        $templates = PdfTemplate::query()
            ->when($organizationId, fn($q) => $q->where('organization_id', $organizationId))
            ->orderBy('created_at', 'desc')
            ->get(['uuid', 'name', 'code', 'layout', 'version', 'active']);

        return Inertia::render('Dashboard', [
            'templates' => $templates,
            'records' => [],
            'template' => null,
            'templateUuid' => null,
        ]);
    })->name('dashboard');


    /*
    |--------------------------------------------------------------------------
    | Templates (UUID based)
    |--------------------------------------------------------------------------
    */

    Route::get('/templates', [TemplateController::class, 'index'])
        ->name('templates.index');

    Route::get('/templates/create', [TemplateController::class, 'create'])
        ->name('templates.create');

    Route::post('/templates', [TemplateController::class, 'store'])
        ->name('templates.store');

    Route::get('/templates/{template:uuid}', [TemplateController::class, 'show'])
        ->name('templates.show');

    Route::get('/templates/{template:uuid}/edit', [TemplateController::class, 'edit'])
        ->name('templates.edit');

    Route::put('/templates/{template:uuid}', [TemplateController::class, 'update'])
        ->name('templates.update');

    Route::patch('/templates/{template:uuid}/layout', [TemplateController::class, 'updateLayout'])
        ->name('templates.update-layout');

    Route::delete('/templates/{template:uuid}', [TemplateController::class, 'destroy'])
        ->name('templates.destroy');

    /*
    | Template Builder
    */
    Route::get('/templates/{template:uuid}/builder', [TemplateController::class, 'builder'])
        ->name('templates.builder');

    /*
    | Template Actions
    */
    Route::post('/templates/{template:uuid}/clone', [TemplateController::class, 'clone'])
        ->name('templates.clone');

    Route::get('/templates/{template:uuid}/export', [TemplateController::class, 'export'])
        ->name('templates.export');

    Route::post('/templates/import', [TemplateController::class, 'import'])
        ->name('templates.import');

    /*
    | PDF Preview
    */
    Route::post('/templates/{template:uuid}/preview', [PdfPreviewController::class, 'preview'])
        ->name('templates.preview');

    Route::post('/templates/{template:uuid}/preview-mock', [PdfPreviewController::class, 'previewMock'])
        ->name('templates.preview-mock');

    /*
    |--------------------------------------------------------------------------
    | API-style access (internal / preview)
    |--------------------------------------------------------------------------
    */
    Route::get('/api/templates/{uuid}', [TemplateController::class, 'getByUuid'])
        ->name('templates.get-by-uuid');

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    Route::get('/invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
    Route::get('/invoices/{invoice}/preview', [InvoiceController::class, 'preview'])->name('invoices.preview');
    Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');
});

require __DIR__ . '/auth.php';
