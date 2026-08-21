@php
    $inputClass = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 disabled:cursor-not-allowed disabled:opacity-50';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
@endphp

<div>
    <x-common.page-breadcrumb pageTitle="Check In" />

    <x-common.component-card title="Check in {{ $patient->full_name }}">
        <p class="mb-5 text-sm text-gray-500 dark:text-gray-400">
            {{ $patient->full_name }}
            <span class="font-mono text-xs text-gray-400">{{ $patient->patient_id }}</span>
        </p>

        @if ($alreadyActive)
            <x-ui.alert variant="warning" title="Already checked in"
                message="This patient already has an active visit today - either waiting/with a practitioner, or a visit invoice sitting with the cashier." />
            <a href="{{ route('patients.show', $patient) }}" class="mt-4 inline-block text-sm text-brand-500 hover:text-brand-600">&larr; Back to patient</a>
        @else
            <form wire:submit="checkIn" class="max-w-2xl space-y-6">
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <div>
                        <label for="department_id" class="{{ $labelClass }}">Department</label>
                        <select id="department_id" wire:model.live="department_id" class="{{ $inputClass }}">
                            <option value="">Select...</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('department_id')" class="mt-2" />
                    </div>
                    <div>
                        <label for="service_id" class="{{ $labelClass }}">Service</label>
                        <select id="service_id" wire:model="service_id" class="{{ $inputClass }}" @disabled(! $department_id)>
                            <option value="">Select...</option>
                            @foreach ($this->services as $service)
                                <option value="{{ $service->id }}">{{ $service->name }} ({{ $service->duration_minutes }} min)</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('service_id')" class="mt-2" />
                    </div>
                    <div>
                        <label for="practitioner_id" class="{{ $labelClass }}">Doctor</label>
                        <select id="practitioner_id" wire:model="practitioner_id" class="{{ $inputClass }}">
                            <option value="">Select...</option>
                            @foreach ($practitioners as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('practitioner_id')" class="mt-2" />
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('patients.show', $patient) }}" class="self-center text-sm text-gray-600 dark:text-gray-300">Cancel</a>
                    <x-ui.button type="submit">{{ __('Check In') }}</x-ui.button>
                </div>
            </form>
        @endif
    </x-common.component-card>
</div>
