@php
    $selectClass = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $typeColors = ['received' => 'success', 'dispensed' => 'info', 'adjusted' => 'warning', 'wasted' => 'error'];
@endphp

<div>
    <x-common.page-breadcrumb pageTitle="Stock Ledger" />

    <div class="space-y-4">
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex flex-wrap items-end gap-3">
                <div class="min-w-[220px] flex-1">
                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Search</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04175 9.37363C3.04175 5.87693 5.87711 3.04199 9.37508 3.04199C12.8731 3.04199 15.7084 5.87693 15.7084 9.37363C15.7084 12.8703 12.8731 15.7053 9.37508 15.7053C5.87711 15.7053 3.04175 12.8703 3.04175 9.37363ZM9.37508 1.54199C5.04902 1.54199 1.54175 5.04817 1.54175 9.37363C1.54175 13.6991 5.04902 17.2053 9.37508 17.2053C11.2674 17.2053 13.003 16.5344 14.357 15.4176L17.177 18.238C17.4699 18.5309 17.9448 18.5309 18.2377 18.238C18.5306 17.9451 18.5306 17.4703 18.2377 17.1774L15.418 14.3573C16.5365 13.0033 17.2084 11.2669 17.2084 9.37363C17.2084 5.04817 13.7011 1.54199 9.37508 1.54199Z" fill="currentColor" />
                            </svg>
                        </span>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Medication name..."
                               class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-11 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Type</label>
                    <select wire:model.live="type" class="{{ $selectClass }}">
                        <option value="">All</option>
                        <option value="received">Received</option>
                        <option value="dispensed">Dispensed</option>
                        <option value="adjusted">Adjusted</option>
                        <option value="wasted">Wasted</option>
                    </select>
                </div>

                @if ($search || $type)
                    <button wire:click="clearFilters" type="button"
                            class="h-11 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                        Clear filters
                    </button>
                @endif

                <div class="ml-auto">
                    <a href="{{ route('inventory.index') }}" class="text-sm font-medium text-brand-500 hover:text-brand-600">&larr; Back to inventory</a>
                </div>
            </div>
        </div>

        <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ $movements->total() }} {{ Str::plural('movement', $movements->total()) }} found
        </p>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="max-w-full overflow-x-auto custom-scrollbar">
                <table class="w-full min-w-[760px]">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Date</p></th>
                            <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Medication</p></th>
                            <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Type</p></th>
                            <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Qty</p></th>
                            <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Reference</p></th>
                            <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">By</p></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($movements as $movement)
                            <tr class="border-b border-gray-100 last:border-0 dark:border-gray-800">
                                <td class="px-5 py-4 text-theme-sm text-gray-500 dark:text-gray-400 sm:px-6">{{ $movement->created_at->format('Y-m-d H:i') }}</td>
                                <td class="px-5 py-4 text-theme-sm text-gray-800 dark:text-white/90 sm:px-6">{{ $movement->medication->name ?? '—' }}</td>
                                <td class="px-5 py-4 sm:px-6">
                                    <x-ui.badge size="sm" variant="solid" :color="$typeColors[$movement->type] ?? 'light'">
                                        {{ ucfirst($movement->type) }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-5 py-4 text-theme-sm sm:px-6 {{ $movement->quantity_delta < 0 ? 'text-error-500' : 'text-success-500' }}">
                                    {{ $movement->quantity_delta > 0 ? '+' : '' }}{{ $movement->quantity_delta }}
                                </td>
                                <td class="px-5 py-4 text-theme-sm text-gray-500 dark:text-gray-400 sm:px-6">
                                    @if ($movement->prescription)
                                        <a href="{{ route('prescriptions.show', $movement->prescription) }}" class="text-brand-500 hover:text-brand-600">
                                            {{ $movement->prescription->patient->full_name }}
                                        </a>
                                    @elseif ($movement->notes)
                                        {{ $movement->notes }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-theme-sm text-gray-500 dark:text-gray-400 sm:px-6">{{ $movement->creator->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-theme-sm text-gray-500 dark:text-gray-400">No stock movements found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 sm:px-6">{{ $movements->links() }}</div>
        </div>
    </div>
</div>
