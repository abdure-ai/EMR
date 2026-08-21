@php
    $inputClass = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
@endphp

<div>
    <x-common.page-breadcrumb pageTitle="Settings" />

    @if (session('status'))
        <div class="mb-4">
            <x-ui.alert variant="success" title="Saved" :message="session('status')" />
        </div>
    @endif

    <x-common.component-card title="Billing rules" desc="Controls how much new and returning patients are charged at check-in.">
        <form wire:submit="save" class="max-w-lg space-y-5">
            <div>
                <label for="new_patient_card_fee" class="{{ $labelClass }}">New patient card fee (ETB)</label>
                <input id="new_patient_card_fee" wire:model="new_patient_card_fee" type="number" step="0.01" min="0" class="{{ $inputClass }}">
                <p class="mt-1 text-xs text-gray-400">Charged once, the first time a patient is ever checked in.</p>
                <x-input-error :messages="$errors->get('new_patient_card_fee')" class="mt-2" />
            </div>

            <div>
                <label for="revisit_free_within_days" class="{{ $labelClass }}">Revisit free window (days)</label>
                <input id="revisit_free_within_days" wire:model="revisit_free_within_days" type="number" min="0" max="365" class="{{ $inputClass }}">
                <p class="mt-1 text-xs text-gray-400">
                    A returning patient checked in within this many days of their last completed visit pays nothing.
                    Beyond that, the service fee applies again.
                </p>
                <x-input-error :messages="$errors->get('revisit_free_within_days')" class="mt-2" />
            </div>

            <div>
                <label for="expiry_alert_days" class="{{ $labelClass }}">Expiry alert window (days)</label>
                <input id="expiry_alert_days" wire:model="expiry_alert_days" type="number" min="1" max="365" class="{{ $inputClass }}">
                <p class="mt-1 text-xs text-gray-400">
                    A stock batch is flagged "expiring soon" on the Pharmacy dashboard once its expiry date is within this many days.
                </p>
                <x-input-error :messages="$errors->get('expiry_alert_days')" class="mt-2" />
            </div>

            <div class="flex justify-end">
                <x-ui.button type="submit">{{ __('Save Settings') }}</x-ui.button>
            </div>
        </form>
    </x-common.component-card>
</div>
