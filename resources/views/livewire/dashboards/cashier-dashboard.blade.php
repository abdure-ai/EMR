<div>
    <x-common.page-breadcrumb pageTitle="Billing" />

    <div class="space-y-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 md:gap-6">
            <x-stat-card label="Outstanding invoices" :value="$pendingInvoices" />
            <x-stat-card label="Revenue today" :value="number_format($revenueToday, 2).' ETB'" />
            <x-stat-card label="Payments today" :value="$paymentsToday" />
        </div>

        <x-common.component-card title="Quick actions">
            <a href="{{ route('billing.index') }}"
               class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                {{ __('Process Payments') }}
            </a>
        </x-common.component-card>
    </div>
</div>
