@php
    $inputClass = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
@endphp

<div>
    <x-common.page-breadcrumb pageTitle="New Department" />

    <x-common.component-card title="New Department">
        <form wire:submit="save" class="max-w-lg space-y-5">
            <div>
                <label for="name" class="{{ $labelClass }}">Name</label>
                <input id="name" wire:model="name" type="text" placeholder="e.g. General Medicine" class="{{ $inputClass }}">
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('departments.index') }}" class="self-center text-sm text-gray-600 dark:text-gray-300">Cancel</a>
                <x-ui.button type="submit">{{ __('Create Department') }}</x-ui.button>
            </div>
        </form>
    </x-common.component-card>
</div>
