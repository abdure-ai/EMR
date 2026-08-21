<div>
    <x-common.page-breadcrumb pageTitle="Pharmacy" />

    <div class="space-y-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-4 md:gap-6">
            <x-stat-card label="Pending prescriptions" :value="$pendingPrescriptions" />
            <x-stat-card label="Dispensed today" :value="$dispensedToday" />
            <x-stat-card label="Low stock" :value="$lowStockCount" hint="Medications at or below reorder level" />
            <x-stat-card label="Expiring soon" :value="$expiringSoonCount" hint="Within the expiry alert window" />
        </div>

        <x-common.component-card title="Quick actions">
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('prescriptions.index') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                    {{ __('Pending Prescriptions') }}
                </a>
                <a href="{{ route('inventory.index') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-lg bg-white px-5 py-3.5 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]">
                    {{ __('Inventory') }}
                </a>
                <a href="{{ route('patients.index') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-lg bg-white px-5 py-3.5 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]">
                    {{ __('Patient List') }}
                </a>
            </div>
        </x-common.component-card>
    </div>
</div>
