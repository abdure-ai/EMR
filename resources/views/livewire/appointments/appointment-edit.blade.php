@php
    $inputClass = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
@endphp

<div>
    <x-common.page-breadcrumb pageTitle="Edit Appointment" />

    <x-common.component-card title="Edit Appointment" :desc="$appointment->patient->full_name">
        <form wire:submit="save" class="max-w-2xl space-y-6">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label for="service_id" class="{{ $labelClass }}">Service</label>
                    <select id="service_id" wire:model="service_id" class="{{ $inputClass }}">
                        @foreach ($services as $service)
                            <option value="{{ $service->id }}">{{ $service->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('service_id')" class="mt-2" />
                </div>
                <div>
                    <label for="practitioner_id" class="{{ $labelClass }}">Practitioner</label>
                    <select id="practitioner_id" wire:model="practitioner_id" class="{{ $inputClass }}">
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
                    <label for="status" class="{{ $labelClass }}">Status</label>
                    <select id="status" wire:model="status" class="{{ $inputClass }}">
                        <option value="booked">Booked</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="awaiting_payment">Awaiting payment</option>
                        <option value="checked_in">Checked in</option>
                        <option value="completed">Completed</option>
                        <option value="no_show">No-show</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <x-input-error :messages="$errors->get('status')" class="mt-2" />
                </div>
            </div>

            <div>
                <label for="notes" class="{{ $labelClass }}">Notes</label>
                <textarea id="notes" wire:model="notes" rows="2" class="{{ $inputClass }}"></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('appointments.index') }}" class="self-center text-sm text-gray-600 dark:text-gray-300">Cancel</a>
                <x-ui.button type="submit">{{ __('Save Changes') }}</x-ui.button>
            </div>
        </form>
    </x-common.component-card>
</div>
