<div>
    <x-common.page-breadcrumb pageTitle="Management" />

    <div class="space-y-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-4 md:gap-6">
            <x-stat-card label="Patients today" :value="$patientsToday" />
            <x-stat-card label="Total patients" :value="$totalPatients" />
            <x-stat-card label="Appointments today" :value="$appointmentsToday" />
            <x-stat-card label="Queue waiting" :value="$queueWaiting" />
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 md:gap-6">
            <x-stat-card label="Active staff" :value="$activeStaff" />
            <x-stat-card label="Revenue today" :value="number_format($revenueToday, 2).' ETB'" />
            <x-stat-card label="Follow-ups due" :value="$followUpsDue" />
        </div>

        <x-common.component-card title="Quick actions">
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('patients.index') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                    {{ __('Patient List') }}
                </a>
                <a href="{{ route('appointments.index') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-lg bg-white px-5 py-3.5 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]">
                    {{ __('Appointments') }}
                </a>
                <a href="{{ route('follow-ups.index') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-lg bg-white px-5 py-3.5 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]">
                    {{ __('Follow-ups') }}
                </a>
                <a href="{{ route('billing.index') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-lg bg-white px-5 py-3.5 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]">
                    {{ __('Billing') }}
                </a>
                <a href="{{ route('services.index') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-lg bg-white px-5 py-3.5 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]">
                    {{ __('Services') }}
                </a>
                <a href="{{ route('settings.index') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-lg bg-white px-5 py-3.5 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]">
                    {{ __('Settings') }}
                </a>
            </div>
        </x-common.component-card>
    </div>
</div>
