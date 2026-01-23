<x-filament::widget>
    <x-filament::card>
        <form wire:submit.prevent="submit">
            {{ $this->form }}
            <br />
            <x-filament::button type="submit" color="primary" class="mt-4">
                Quick Patient Search
            </x-filament::button>
        </form>

        @if ($foundPatient)
        <!-- Patient found - show details or redirect to patient page -->
        <div class="mt-6 p-4 bg-green-50 rounded-lg">
            <h3 class="text-lg font-semibold text-green-800">Patient Found!</h3>
            <p class="mt-2 text-green-700">
                {{ $foundPatient->first_name }} {{ $foundPatient->last_name }}
                (MRN: {{ $foundPatient->mrn }})
            </p>
            <div class="mt-4">
                <x-filament::button
                    tag="a"
                    href="{{ route('filament.dashboard.resources.patients.view', $foundPatient) }}"
                    color="success"
                    class="mt-2">
                    View Patient
                </x-filament::button>
            </div>
        </div>
        @elseif ($searchAttempted && empty($foundPatient))
        <!-- No patient found, show alert box -->
        <div class="mt-6 rounded-md bg-yellow-50 p-4 border border-yellow-300">
            <div class="flex">
                <div class="flex-shrink-0">
                    <!-- Heroicon: exclamation-triangle -->
                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.721-1.36 3.486 0l6.518 11.6c.75 1.336-.213 3.001-1.743 3.001H3.482c-1.53 0-2.493-1.665-1.743-3.001l6.518-11.6zM11 14a1 1 0 10-2 0 1 1 0 002 0zm-.25-6.75a.75.75 0 00-1.5 0v3.5a.75.75 0 001.5 0v-3.5z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">No Patient Found</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        Would you like to create a new patient with the entered details?
                    </div>
                    <div class="mt-4">
                        <x-filament::button
                            tag="a"
                            href="{{ $this->getCreateUrl() }}"
                            color="success"
                            class="mt-2">
                            Create New Patient with These Details
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </x-filament::card>
</x-filament::widget>