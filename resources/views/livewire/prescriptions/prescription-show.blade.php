@php
    $statusColors = ['pending' => 'warning', 'dispensed' => 'success', 'cancelled' => 'error'];
@endphp

<div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                {{ $prescription->patient->full_name }}
                <span class="ms-2 font-mono text-sm text-gray-400">{{ $prescription->patient->patient_id }}</span>
                <x-ui.badge size="sm" variant="solid" :color="$statusColors[$prescription->status] ?? 'light'">
                    {{ ucfirst($prescription->status) }}
                </x-ui.badge>
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ $prescription->created_at->format('Y-m-d, H:i') }}
                @if ($prescription->practitioner)
                    &middot; Prescribed by {{ $prescription->practitioner->name }}
                @endif
            </p>
        </div>
        <a href="{{ route('prescriptions.index') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">&larr; Back to prescriptions</a>
    </div>

    <div class="space-y-6">
        @if (session('status'))
            <x-ui.alert variant="success" title="Done" :message="session('status')" />
        @endif

        <x-common.component-card title="Items">
            <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                            <th class="px-4 py-2.5 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Medication</th>
                            <th class="px-4 py-2.5 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Dosage</th>
                            <th class="px-4 py-2.5 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Frequency</th>
                            <th class="px-4 py-2.5 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Duration</th>
                            <th class="px-4 py-2.5 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Qty</th>
                            <th class="px-4 py-2.5 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Instructions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($prescription->items as $item)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-800 dark:text-white/90">{{ $item->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $item->dosage ?: '—' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $item->frequency ?: '—' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $item->duration ?: '—' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $item->quantity ?: '—' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $item->instructions ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-common.component-card>

        @if ($prescription->status === 'dispensed')
            <x-common.component-card title="Dispensed">
                <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                    <div><dt class="text-gray-500 dark:text-gray-400">Dispensed at</dt><dd class="text-gray-800 dark:text-white/90">{{ $prescription->dispensed_at?->format('Y-m-d H:i') }}</dd></div>
                    <div><dt class="text-gray-500 dark:text-gray-400">Dispensed by</dt><dd class="text-gray-800 dark:text-white/90">{{ $prescription->dispenser->name ?? '—' }}</dd></div>
                </dl>
            </x-common.component-card>
        @elseif ($canDispense)
            <x-common.component-card title="Dispense">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Confirm the items above have been handed to the patient.</p>
                    <x-ui.button wire:click="dispense" wire:confirm="Mark this prescription as dispensed?">{{ __('Dispense') }}</x-ui.button>
                </div>
            </x-common.component-card>
        @endif
    </div>
</div>
