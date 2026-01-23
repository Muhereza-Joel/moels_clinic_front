<?php

namespace App\Filament\Resources\PatientResource\Widgets;

use App\Models\Patient;
use Filament\Forms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Blade;

class QuickPatientSearch extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.resources.patient-resource.widgets.quick-patient-search';

    protected int|string|array $columnSpan = 'full';

    public ?array $data = [];
    public ?Patient $foundPatient = null;

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Basic Info')
                        ->schema([
                            Forms\Components\Section::make('Patient Info')
                                ->columns(5)
                                ->schema([
                                    Forms\Components\TextInput::make('first_name')
                                        ->label('First Name')
                                        ->placeholder('Enter first name'),

                                    Forms\Components\TextInput::make('last_name')
                                        ->label('Last Name')
                                        ->placeholder('Enter last name'),

                                    Forms\Components\Select::make('sex')
                                        ->label('Gender')
                                        ->options([
                                            'Male' => 'Male',
                                            'Female' => 'Female',
                                            'Other' => 'Other',
                                        ])
                                        ->searchable()
                                        ->placeholder('Select gender'),

                                    Forms\Components\DatePicker::make('date_of_birth')
                                        ->label('Date of Birth')
                                        ->native(false)
                                        ->placeholder('Pick a date'),
                                ]),
                        ]),

                    Forms\Components\Wizard\Step::make('Contacts')
                        ->schema([
                            Forms\Components\Section::make('Contact Details')
                                ->columns(3)
                                ->schema([
                                    Forms\Components\TextInput::make('phone')
                                        ->label('Phone Number')
                                        ->tel()
                                        ->placeholder('Enter phone number'),

                                    Forms\Components\TextInput::make('email')
                                        ->label('Email Address')
                                        ->email()
                                        ->placeholder('Enter email'),

                                    Forms\Components\TextInput::make('emergency_contact')
                                        ->label('Emergency Contact')
                                        ->placeholder('Enter emergency contact'),

                                    Forms\Components\TextInput::make('address')
                                        ->label('Address')
                                        ->placeholder('Enter address'),
                                ]),
                        ]),

                    Forms\Components\Wizard\Step::make('Other Info')
                        ->schema([
                            Forms\Components\Section::make('Additional Info')
                                ->columns(2)
                                ->schema([
                                    Forms\Components\TextInput::make('national_id')
                                        ->label('NIN Number')
                                        ->placeholder('Enter NIN')
                                        ->maxLength(20),

                                    Forms\Components\Textarea::make('notes')
                                        ->label('Notes')
                                        ->placeholder('Any extra notes...'),
                                ]),
                        ]),
                ])
                    ->skippable()
                    ->persistStepInQueryString()
                    ->statePath('data'),
            ]);
    }

    public bool $searchAttempted = false;

    public function submit(): void
    {
        $this->searchAttempted = true;

        $query = Patient::query();

        foreach ($this->data as $field => $value) {
            if (!empty($value)) {
                if (in_array($field, ['first_name', 'last_name', 'national_id', 'email', 'address', 'notes', 'phone', 'emergency_contact'])) {
                    $query->where($field, 'like', "%{$value}%");
                } elseif ($field === 'date_of_birth') {
                    $query->whereDate($field, $value);
                } else {
                    $query->where($field, $value);
                }
            }
        }

        $this->foundPatient = $query->first();

        if ($this->foundPatient) {
            Notification::make()
                ->title('Patient Found!')
                ->body("{$this->foundPatient->first_name} {$this->foundPatient->last_name} (MRN: {$this->foundPatient->mrn})")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('No Patient Found')
                ->body('Click "Create New Patient" to create a new record with the entered details.')
                ->warning()
                ->send();
        }
    }


    // Create a method to generate the URL with form data
    public function getCreateUrl(): string
    {
        $queryParams = [];

        foreach ($this->data as $key => $value) {
            if (!empty($value)) {
                if ($key === 'date_of_birth') {
                    // Ensure it's a string in Y-m-d format
                    if ($value instanceof \Carbon\Carbon) {
                        $value = $value->format('Y-m-d');
                    } else {
                        try {
                            $value = \Carbon\Carbon::parse($value)->format('Y-m-d');
                        } catch (\Exception $e) {
                            continue; // skip invalid date
                        }
                    }
                }

                if ($key === 'sex') {
                    // Normalize case to match your options
                    $value = ucfirst(strtolower($value));
                }

                $queryParams[$key] = $value;
            }
        }

        $url = route('filament.dashboard.resources.patients.create');

        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

        return $url;
    }
}
