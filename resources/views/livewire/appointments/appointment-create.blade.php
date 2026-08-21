@php
    $inputClass = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
@endphp

<div>
    <x-common.page-breadcrumb pageTitle="Book Appointment" />

    <x-common.component-card title="Book Appointment">
        <form wire:submit="save" class="max-w-2xl space-y-6">
            <div>
                <label class="{{ $labelClass }}">Patient</label>

                @if ($selectedPatientLabel)
                    <div class="flex items-center justify-between rounded-lg border border-gray-300 px-4 py-2.5 text-sm dark:border-gray-700">
                        <span class="text-gray-800 dark:text-white/90">{{ $selectedPatientLabel }}</span>
                        <button type="button" wire:click="clearPatient" class="text-xs text-gray-500 hover:text-error-500 dark:text-gray-400">Change</button>
                    </div>
                @else
                    <div class="relative">
                        <input type="text" wire:model.live.debounce.300ms="patientSearch"
                               placeholder="Search by ID, name, or phone..." class="{{ $inputClass }}">

                        @if ($this->patientResults->isNotEmpty())
                            <div class="absolute z-10 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark">
                                @foreach ($this->patientResults as $result)
                                    <button type="button" wire:click="selectPatient({{ $result->id }})"
                                            class="flex w-full items-center justify-between px-4 py-2.5 text-left text-sm hover:bg-gray-50 dark:hover:bg-white/5">
                                        <span class="text-gray-800 dark:text-white/90">{{ $result->full_name }}</span>
                                        <span class="font-mono text-xs text-gray-400">{{ $result->patient_id }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
                <x-input-error :messages="$errors->get('patient_id')" class="mt-2" />
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label for="service_id" class="{{ $labelClass }}">Service</label>
                    <select id="service_id" wire:model="service_id" class="{{ $inputClass }}">
                        <option value="">Select...</option>
                        @foreach ($services as $service)
                            <option value="{{ $service->id }}">{{ $service->name }} ({{ $service->duration_minutes }} min)</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('service_id')" class="mt-2" />
                </div>
                <div>
                    <label for="practitioner_id" class="{{ $labelClass }}">Practitioner</label>
                    <select id="practitioner_id" wire:model="practitioner_id" class="{{ $inputClass }}">
                        <option value="">Select...</option>
                        @foreach ($practitioners as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('practitioner_id')" class="mt-2" />
                </div>

                <div>
                    <label for="scheduled_date" class="{{ $labelClass }}">Date</label>
                    <input id="scheduled_date" wire:model="scheduled_date" type="date" class="{{ $inputClass }}">
                    <x-input-error :messages="$errors->get('scheduled_date')" class="mt-2" />
                </div>
                <div>
                    <label for="scheduled_time" class="{{ $labelClass }}">Time</label>
                    <input id="scheduled_time" wire:model="scheduled_time" type="time" class="{{ $inputClass }}">
                    <x-input-error :messages="$errors->get('scheduled_time')" class="mt-2" />
                </div>

                <div>
                    <label for="source" class="{{ $labelClass }}">Source</label>
                    <select id="source" wire:model="source" class="{{ $inputClass }}">
                        <option value="phone">Phone</option>
                        <option value="walk-in">Walk-in</option>
                        <option value="website">Website</option>
                        <option value="referral">Referral</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="notes" class="{{ $labelClass }}">Notes (optional)</label>
                <textarea id="notes" wire:model="notes" rows="2" class="{{ $inputClass }}"></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('appointments.index') }}" class="self-center text-sm text-gray-600 dark:text-gray-300">Cancel</a>
                <x-ui.button type="submit">{{ __('Book Appointment') }}</x-ui.button>
            </div>
        </form>
    </x-common.component-card>
</div>
