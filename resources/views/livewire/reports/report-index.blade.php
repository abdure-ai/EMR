@php
    $selectClass = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $presetLabel = fn ($value, $label) => $preset === $value
        ? 'inline-flex items-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white'
        : 'inline-flex items-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]';
@endphp

<div>
    <x-common.page-breadcrumb pageTitle="Reports" />

    <div class="space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex flex-wrap gap-2">
                    <button wire:click="setPreset('today')" type="button" class="{{ $presetLabel('today', 'Today') }}">Today</button>
                    <button wire:click="setPreset('this_week')" type="button" class="{{ $presetLabel('this_week', 'This Week') }}">This Week</button>
                    <button wire:click="setPreset('this_month')" type="button" class="{{ $presetLabel('this_month', 'This Month') }}">This Month</button>
                    <button wire:click="setPreset('last_30_days')" type="button" class="{{ $presetLabel('last_30_days', 'Last 30 Days') }}">Last 30 Days</button>
                    <button wire:click="setPreset('this_year')" type="button" class="{{ $presetLabel('this_year', 'This Year') }}">This Year</button>
                </div>

                <div class="ml-auto flex flex-wrap items-end gap-3">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">From</label>
                        <input type="date" wire:model.live="from" class="{{ $selectClass }}">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">To</label>
                        <input type="date" wire:model.live="to" class="{{ $selectClass }}">
                    </div>
                    <button wire:click="exportCsv" type="button"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                        <svg width="18" height="18" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M10 1.5a.75.75 0 0 1 .75.75v8.69l2.72-2.72a.75.75 0 1 1 1.06 1.06l-4 4a.75.75 0 0 1-1.06 0l-4-4a.75.75 0 1 1 1.06-1.06l2.72 2.72V2.25A.75.75 0 0 1 10 1.5ZM3.75 13a.75.75 0 0 1 .75.75v2.5c0 .414.336.75.75.75h9.5a.75.75 0 0 0 .75-.75v-2.5a.75.75 0 0 1 1.5 0v2.5A2.25 2.25 0 0 1 14.75 18.5h-9.5A2.25 2.25 0 0 1 3 16.25v-2.5a.75.75 0 0 1 .75-.75Z" fill="currentColor"/>
                        </svg>
                        Export CSV
                    </button>
                </div>
            </div>
        </div>

        <p class="text-sm text-gray-500 dark:text-gray-400">Showing data for <span class="font-medium text-gray-700 dark:text-gray-300">{{ $rangeLabel }}</span></p>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-6 xl:grid-cols-5">
            <x-stat-card label="Revenue" :value="number_format($summary['revenue'], 2).' ETB'" />
            <x-stat-card label="New Patients" :value="$summary['newPatients']" />
            <x-stat-card label="Visits Completed" :value="$summary['visitsCompleted']" />
            <x-stat-card label="Prescriptions Dispensed" :value="$summary['prescriptionsDispensed']" />
            <x-stat-card label="Outstanding (Pending Invoices)" :value="number_format($summary['outstanding'], 2).' ETB'" />
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
            <h3 class="mb-4 text-base font-semibold text-gray-800 dark:text-white/90">Revenue Trend</h3>
            <div wire:ignore x-data="reportRevenueChart(@js($trend['series']), @js($trend['categories']))">
                <div x-ref="canvas"></div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:gap-6 xl:grid-cols-2">
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Revenue by Service</h3>
                </div>
                <div class="max-w-full overflow-x-auto custom-scrollbar">
                    <table class="w-full min-w-[420px]">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <th class="px-5 py-3 text-left"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Service</p></th>
                                <th class="px-5 py-3 text-left"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Invoices</p></th>
                                <th class="px-5 py-3 text-right"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Revenue</p></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($byService as $row)
                                <tr class="border-b border-gray-100 last:border-0 dark:border-gray-800">
                                    <td class="px-5 py-3 text-theme-sm text-gray-800 dark:text-white/90">{{ $row->service_name }}</td>
                                    <td class="px-5 py-3 text-theme-sm text-gray-500 dark:text-gray-400">{{ $row->invoice_count }}</td>
                                    <td class="px-5 py-3 text-right text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ number_format($row->total, 2) }} ETB</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-5 py-6 text-center text-theme-sm text-gray-500 dark:text-gray-400">No revenue in this range.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Revenue by Practitioner</h3>
                </div>
                <div class="max-w-full overflow-x-auto custom-scrollbar">
                    <table class="w-full min-w-[420px]">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <th class="px-5 py-3 text-left"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Practitioner</p></th>
                                <th class="px-5 py-3 text-left"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Visits</p></th>
                                <th class="px-5 py-3 text-right"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Revenue</p></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($byPractitioner as $row)
                                <tr class="border-b border-gray-100 last:border-0 dark:border-gray-800">
                                    <td class="px-5 py-3 text-theme-sm text-gray-800 dark:text-white/90">{{ $row->practitioner_name }}</td>
                                    <td class="px-5 py-3 text-theme-sm text-gray-500 dark:text-gray-400">{{ $row->invoice_count }}</td>
                                    <td class="px-5 py-3 text-right text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ number_format($row->total, 2) }} ETB</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-5 py-6 text-center text-theme-sm text-gray-500 dark:text-gray-400">No revenue in this range.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Revenue by Payment Method</h3>
            </div>
            <div class="max-w-full overflow-x-auto custom-scrollbar">
                <table class="w-full min-w-[420px]">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-5 py-3 text-left"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Method</p></th>
                            <th class="px-5 py-3 text-left"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Payments</p></th>
                            <th class="px-5 py-3 text-right"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Revenue</p></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($byMethod as $row)
                            <tr class="border-b border-gray-100 last:border-0 dark:border-gray-800">
                                <td class="px-5 py-3 text-theme-sm capitalize text-gray-800 dark:text-white/90">{{ str_replace('_', ' ', $row->method) }}</td>
                                <td class="px-5 py-3 text-theme-sm text-gray-500 dark:text-gray-400">{{ $row->payment_count }}</td>
                                <td class="px-5 py-3 text-right text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ number_format($row->total, 2) }} ETB</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-5 py-6 text-center text-theme-sm text-gray-500 dark:text-gray-400">No payments in this range.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
