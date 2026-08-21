@php
    $selectClass = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $statusColors = ['pending' => 'warning', 'paid' => 'success', 'waived' => 'light', 'cancelled' => 'error'];
@endphp

<div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                {{ $invoice->invoice_number }}
                <x-ui.badge size="sm" variant="solid" :color="$statusColors[$invoice->status] ?? 'light'">{{ ucfirst($invoice->status) }}</x-ui.badge>
                <x-ui.badge size="sm" color="light">{{ $invoice->type === 'registration' ? 'Registration' : 'Visit' }}</x-ui.badge>
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $invoice->created_at->format('Y-m-d H:i') }}</p>
        </div>
        <a href="{{ route('billing.index') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">&larr; Back to billing</a>
    </div>

    <div class="space-y-6">
        @if (session('status'))
            <x-ui.alert variant="success" title="Done" :message="session('status')" />
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <x-common.component-card title="Patient & visit">
                <dl class="space-y-4 text-sm">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Patient</dt>
                        <dd>
                            <a href="{{ route('patients.show', $invoice->patient) }}" class="text-brand-500 hover:text-brand-600">
                                {{ $invoice->patient->full_name }}
                            </a>
                            <span class="font-mono text-xs text-gray-400">{{ $invoice->patient->patient_id }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Practitioner</dt>
                        <dd class="text-gray-800 dark:text-white/90">{{ $invoice->practitioner->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Service</dt>
                        <dd class="text-gray-800 dark:text-white/90">{{ $invoice->service->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Sent by</dt>
                        <dd class="text-gray-800 dark:text-white/90">{{ $invoice->creator->name ?? '—' }}</dd>
                    </div>
                </dl>
            </x-common.component-card>

            <x-common.component-card title="Charges">
                <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800">
                    <table class="w-full">
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($invoice->lineItems as $item)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $item->description }}</td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-700 dark:text-gray-300">{{ number_format($item->amount, 2) }} ETB</td>
                                </tr>
                            @endforeach
                            <tr class="bg-gray-50 dark:bg-white/[0.03]">
                                <td class="px-4 py-3 text-sm font-semibold text-gray-800 dark:text-white/90">Total</td>
                                <td class="px-4 py-3 text-right text-sm font-semibold text-gray-800 dark:text-white/90">{{ number_format($invoice->total_amount, 2) }} ETB</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </x-common.component-card>
        </div>

        @if ($invoice->status === 'pending')
            @can('update', $invoice)
                <x-common.component-card title="Process payment">
                    <form wire:submit="confirmPayment" class="flex flex-wrap items-end gap-3">
                        <div class="min-w-[200px]">
                            <label for="paymentMethod" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Method</label>
                            <select id="paymentMethod" wire:model="paymentMethod" class="{{ $selectClass }}">
                                <option value="cash">Cash</option>
                                <option value="bank">Bank</option>
                                <option value="telebirr">Telebirr</option>
                                <option value="cbe_birr">CBE Birr</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <x-ui.button type="submit">{{ __('Confirm Payment') }}</x-ui.button>
                    </form>
                </x-common.component-card>
            @endcan
        @elseif ($invoice->status === 'paid')
            <x-common.component-card title="Payment">
                <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                    <div><dt class="text-gray-500 dark:text-gray-400">Paid at</dt><dd class="text-gray-800 dark:text-white/90">{{ $invoice->paid_at?->format('Y-m-d H:i') }}</dd></div>
                    <div><dt class="text-gray-500 dark:text-gray-400">Processed by</dt><dd class="text-gray-800 dark:text-white/90">{{ $invoice->processor->name ?? '—' }}</dd></div>
                    @foreach ($invoice->payments as $payment)
                        <div><dt class="text-gray-500 dark:text-gray-400">Method</dt><dd class="text-gray-800 capitalize dark:text-white/90">{{ str_replace('_', ' ', $payment->method) }}</dd></div>
                    @endforeach
                </dl>
            </x-common.component-card>
        @endif
    </div>
</div>
