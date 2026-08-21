@php
    $inputClass = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
@endphp

<div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $medication->name }}</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ $medication->strength ?: '' }} {{ $medication->form ? "· {$medication->form}" : '' }}
                &middot; On hand: {{ $batches->sum('quantity_remaining') }}
                @if ($medication->reorder_level !== null)
                    &middot; Reorder level: {{ $medication->reorder_level }}
                @endif
            </p>
        </div>
        <a href="{{ route('inventory.index') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">&larr; Back to inventory</a>
    </div>

    <div class="space-y-6">
        @if (session('status'))
            <x-ui.alert variant="success" title="Done" :message="session('status')" />
        @endif

        @if ($canManage)
            <x-common.component-card title="Receive stock">
                <form wire:submit="receiveStock" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label for="batch_number" class="{{ $labelClass }}">Batch / lot number</label>
                        <input id="batch_number" wire:model="batch_number" type="text" class="{{ $inputClass }}">
                        <x-input-error :messages="$errors->get('batch_number')" class="mt-2" />
                    </div>
                    <div>
                        <label for="quantity" class="{{ $labelClass }}">Quantity</label>
                        <input id="quantity" wire:model="quantity" type="number" min="1" class="{{ $inputClass }}">
                        <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                    </div>
                    <div>
                        <label for="unit_cost" class="{{ $labelClass }}">Unit cost (ETB, optional)</label>
                        <input id="unit_cost" wire:model="unit_cost" type="number" step="0.01" min="0" class="{{ $inputClass }}">
                        <x-input-error :messages="$errors->get('unit_cost')" class="mt-2" />
                    </div>
                    <div>
                        <label for="expiry_date" class="{{ $labelClass }}">Expiry date (optional)</label>
                        <input id="expiry_date" wire:model="expiry_date" type="date" class="{{ $inputClass }}">
                        <x-input-error :messages="$errors->get('expiry_date')" class="mt-2" />
                    </div>
                    <div>
                        <label for="received_at" class="{{ $labelClass }}">Received date</label>
                        <input id="received_at" wire:model="received_at" type="date" class="{{ $inputClass }}">
                        <x-input-error :messages="$errors->get('received_at')" class="mt-2" />
                    </div>
                    <div>
                        <label for="notes" class="{{ $labelClass }}">Notes (optional)</label>
                        <input id="notes" wire:model="notes" type="text" class="{{ $inputClass }}">
                        <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                    </div>
                    <div class="sm:col-span-2 lg:col-span-3 flex justify-end">
                        <x-ui.button type="submit">{{ __('Receive Stock') }}</x-ui.button>
                    </div>
                </form>
            </x-common.component-card>
        @endif

        <x-common.component-card title="Batches">
            <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                            <th class="px-4 py-2.5 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Batch</th>
                            <th class="px-4 py-2.5 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Received</th>
                            <th class="px-4 py-2.5 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Remaining</th>
                            <th class="px-4 py-2.5 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Expiry</th>
                            <th class="px-4 py-2.5 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Status</th>
                            @if ($canManage)
                                <th class="px-4 py-2.5 text-right text-theme-xs font-medium text-gray-500 dark:text-gray-400">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($batches as $batch)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-800 dark:text-white/90">{{ $batch->batch_number ?: '—' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $batch->received_at->format('Y-m-d') }} ({{ $batch->quantity_received }})</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $batch->quantity_remaining }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $batch->expiry_date?->format('Y-m-d') ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    @if ($batch->quantity_remaining <= 0)
                                        <x-ui.badge size="sm" variant="solid" color="light">Depleted</x-ui.badge>
                                    @elseif ($batch->isExpired())
                                        <x-ui.badge size="sm" variant="solid" color="error">Expired</x-ui.badge>
                                    @elseif ($batch->isExpiringSoon($expiryAlertDays ?? 30))
                                        <x-ui.badge size="sm" variant="solid" color="warning">Expiring soon</x-ui.badge>
                                    @else
                                        <x-ui.badge size="sm" variant="solid" color="success">Active</x-ui.badge>
                                    @endif
                                </td>
                                @if ($canManage)
                                    <td class="px-4 py-3 text-right">
                                        @if ($adjustingBatchId === $batch->id)
                                            <form wire:submit="adjustStock" class="flex items-center justify-end gap-2">
                                                <input wire:model="adjustDelta" type="number" placeholder="+/-" class="h-9 w-20 rounded-lg border border-gray-300 bg-transparent px-2 text-sm dark:border-gray-700 dark:text-white/90">
                                                <input wire:model="adjustReason" type="text" placeholder="Reason" class="h-9 w-32 rounded-lg border border-gray-300 bg-transparent px-2 text-sm dark:border-gray-700 dark:text-white/90">
                                                <button type="submit" class="text-sm text-brand-500 hover:text-brand-600">Save</button>
                                                <button type="button" wire:click="cancelAdjusting" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400">Cancel</button>
                                            </form>
                                        @else
                                            <button wire:click="startAdjusting({{ $batch->id }})" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400">Adjust</button>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $canManage ? 6 : 5 }}" class="px-4 py-6 text-center text-sm text-gray-400">No stock received yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-common.component-card>
    </div>
</div>
