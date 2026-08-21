<div>
    <x-common.page-breadcrumb pageTitle="Billing" />

    <div class="space-y-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 md:gap-6">
            <x-stat-card label="Pending invoices" :value="$pendingCount" />
            <x-stat-card label="Revenue today" :value="number_format($revenueToday, 2).' ETB'" />
            <x-stat-card label="Payments today" :value="$paidTodayCount" />
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex flex-wrap items-end gap-3">
                <div class="min-w-[260px] flex-1">
                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Search</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04175 9.37363C3.04175 5.87693 5.87711 3.04199 9.37508 3.04199C12.8731 3.04199 15.7084 5.87693 15.7084 9.37363C15.7084 12.8703 12.8731 15.7053 9.37508 15.7053C5.87711 15.7053 3.04175 12.8703 3.04175 9.37363ZM9.37508 1.54199C5.04902 1.54199 1.54175 5.04817 1.54175 9.37363C1.54175 13.6991 5.04902 17.2053 9.37508 17.2053C11.2674 17.2053 13.003 16.5344 14.357 15.4176L17.177 18.238C17.4699 18.5309 17.9448 18.5309 18.2377 18.238C18.5306 17.9451 18.5306 17.4703 18.2377 17.1774L15.418 14.3573C16.5365 13.0033 17.2084 11.2669 17.2084 9.37363C17.2084 5.04817 13.7011 1.54199 9.37508 1.54199Z" fill="currentColor" />
                            </svg>
                        </span>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Invoice number, patient ID, or name..."
                               class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-11 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                    </div>
                </div>

                @if ($search)
                    <button wire:click="clearFilters" type="button"
                            class="h-11 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                        Clear filters
                    </button>
                @endif
            </div>
        </div>

        @if (session('status'))
            <x-ui.alert variant="success" title="Done" :message="session('status')" />
        @endif

        <x-common.component-card title="Pending payments" desc="Sent over from reception - open a row to review and process before the patient joins the queue.">
            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                <div class="max-w-full overflow-x-auto custom-scrollbar">
                    <table class="w-full min-w-[760px]">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Invoice</p></th>
                                <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Patient</p></th>
                                <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Type</p></th>
                                <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Status</p></th>
                                <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Amount</p></th>
                                <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Sent</p></th>
                                <th class="px-5 py-3 text-right sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Actions</p></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pendingInvoices as $invoice)
                                <tr class="border-b border-gray-100 last:border-0 dark:border-gray-800">
                                    <td class="px-5 py-4 font-mono text-theme-sm text-gray-800 dark:text-white/90 sm:px-6">{{ $invoice->invoice_number }}</td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-800 dark:text-white/90 sm:px-6">{{ $invoice->patient->full_name }}</td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <x-ui.badge size="sm" color="light">{{ $invoice->type === 'registration' ? 'Registration' : 'Visit' }}</x-ui.badge>
                                    </td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <x-ui.badge size="sm" variant="solid" color="warning">Pending</x-ui.badge>
                                    </td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-500 dark:text-gray-400 sm:px-6">{{ number_format($invoice->total_amount, 2) }} ETB</td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-500 dark:text-gray-400 sm:px-6">{{ $invoice->created_at->format('H:i') }}</td>
                                    <td class="px-5 py-4 text-right sm:px-6">
                                        <a href="{{ route('billing.show', $invoice) }}" title="View" class="inline-flex text-gray-400 hover:text-brand-500">
                                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M10 4.375c-4.66 0-7.836 3.9-8.62 5.02a.75.75 0 0 0 0 .81c.784 1.12 3.96 5.02 8.62 5.02s7.836-3.9 8.62-5.02a.75.75 0 0 0 0-.81C17.836 8.275 14.66 4.375 10 4.375Zm0 8.75a3.125 3.125 0 1 1 0-6.25 3.125 3.125 0 0 1 0 6.25Z" fill="currentColor"/>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-8 text-center text-theme-sm text-gray-500 dark:text-gray-400">Nothing waiting on payment right now.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-5 py-4 sm:px-6">{{ $pendingInvoices->links() }}</div>
            </div>
        </x-common.component-card>

        <x-common.component-card title="Paid today">
            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                <div class="max-w-full overflow-x-auto custom-scrollbar">
                    <table class="w-full min-w-[640px]">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Invoice</p></th>
                                <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Patient</p></th>
                                <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Status</p></th>
                                <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Amount</p></th>
                                <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Paid at</p></th>
                                <th class="px-5 py-3 text-right sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Actions</p></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($paidToday as $invoice)
                                <tr class="border-b border-gray-100 last:border-0 dark:border-gray-800">
                                    <td class="px-5 py-4 font-mono text-theme-sm text-gray-800 dark:text-white/90 sm:px-6">{{ $invoice->invoice_number }}</td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-800 dark:text-white/90 sm:px-6">{{ $invoice->patient->full_name }}</td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <x-ui.badge size="sm" variant="solid" color="success">Paid</x-ui.badge>
                                    </td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-500 dark:text-gray-400 sm:px-6">{{ number_format($invoice->total_amount, 2) }} ETB</td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-500 dark:text-gray-400 sm:px-6">{{ $invoice->paid_at->format('H:i') }}</td>
                                    <td class="px-5 py-4 text-right sm:px-6">
                                        <a href="{{ route('billing.show', $invoice) }}" title="View" class="inline-flex text-gray-400 hover:text-brand-500">
                                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M10 4.375c-4.66 0-7.836 3.9-8.62 5.02a.75.75 0 0 0 0 .81c.784 1.12 3.96 5.02 8.62 5.02s7.836-3.9 8.62-5.02a.75.75 0 0 0 0-.81C17.836 8.275 14.66 4.375 10 4.375Zm0 8.75a3.125 3.125 0 1 1 0-6.25 3.125 3.125 0 0 1 0 6.25Z" fill="currentColor"/>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-8 text-center text-theme-sm text-gray-500 dark:text-gray-400">No payments recorded yet today.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-5 py-4 sm:px-6">{{ $paidToday->links() }}</div>
            </div>
        </x-common.component-card>
    </div>
</div>
