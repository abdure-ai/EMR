<div>
<x-common.page-breadcrumb pageTitle="Reception" />

<div class="space-y-6">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 lg:grid-cols-6 md:gap-6">
        <x-stat-card label="Patients registered today" :value="$patientsToday" />
        <x-stat-card label="Total patients" :value="$totalPatients" />
        <x-stat-card label="Appointments today" :value="$appointmentsToday" />
        <x-stat-card label="Queue / waiting" :value="$queueWaiting" />
        <x-stat-card label="Awaiting payment" :value="$awaitingPayment" hint="Sent to cashier" />
        <x-stat-card label="Follow-ups due" :value="$followUpsDue" />
    </div>

    <x-common.component-card title="Quick actions">
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('patients.create') }}"
               class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                {{ __('Register Patient') }}
            </a>
            <a href="{{ route('appointments.create') }}"
               class="inline-flex items-center justify-center gap-2 rounded-lg bg-white px-5 py-3.5 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]">
                {{ __('Book Appointment') }}
            </a>
            <a href="{{ route('patients.index') }}"
               class="inline-flex items-center justify-center gap-2 rounded-lg bg-white px-5 py-3.5 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]">
                {{ __('Patient List') }}
            </a>
            <a href="{{ route('follow-ups.index') }}"
               class="inline-flex items-center justify-center gap-2 rounded-lg bg-white px-5 py-3.5 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]">
                {{ __('Follow-ups') }}
            </a>
        </div>
    </x-common.component-card>
</div>
</div>
