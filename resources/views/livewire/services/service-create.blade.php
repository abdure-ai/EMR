@php
    $inputClass = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
@endphp

<div>
    <x-common.page-breadcrumb pageTitle="New Service" />

    <x-common.component-card title="New Service">
        <form wire:submit="save" class="max-w-lg space-y-5">
            <div>
                <label for="department_id" class="{{ $labelClass }}">Department</label>
                <select id="department_id" wire:model="department_id" class="{{ $inputClass }}">
                    <option value="">Select...</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('department_id')" class="mt-2" />
            </div>
            <div>
                <label for="name" class="{{ $labelClass }}">Name</label>
                <input id="name" wire:model="name" type="text" placeholder="e.g. General Consultation" class="{{ $inputClass }}">
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>
            <div>
                <label for="duration_minutes" class="{{ $labelClass }}">Duration (minutes)</label>
                <input id="duration_minutes" wire:model="duration_minutes" type="number" min="5" max="480" class="{{ $inputClass }}">
                <x-input-error :messages="$errors->get('duration_minutes')" class="mt-2" />
            </div>
            <div>
                <label for="price" class="{{ $labelClass }}">Price (ETB, optional)</label>
                <input id="price" wire:model="price" type="number" step="0.01" min="0" class="{{ $inputClass }}">
                <x-input-error :messages="$errors->get('price')" class="mt-2" />
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('services.index') }}" class="self-center text-sm text-gray-600 dark:text-gray-300">Cancel</a>
                <x-ui.button type="submit">{{ __('Create Service') }}</x-ui.button>
            </div>
        </form>
    </x-common.component-card>
</div>
