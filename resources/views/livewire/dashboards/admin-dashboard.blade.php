<div>
    <x-common.page-breadcrumb pageTitle="Administration" />

    <div class="space-y-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 md:gap-6">
            <x-stat-card label="Total users" :value="$totalUsers" />
            <x-stat-card label="Total patients" :value="$totalPatients" />
            <x-stat-card label="Roles" :value="$roleCounts->count()" />
        </div>

        <x-common.component-card title="Users by role">
            <ul class="space-y-1 text-sm text-gray-700 dark:text-gray-300">
                @foreach ($roleCounts as $role)
                    <li class="flex justify-between border-b border-gray-100 py-2 last:border-0 dark:border-gray-800">
                        <span>{{ $role->name }}</span>
                        <span class="text-gray-500 dark:text-gray-400">{{ $role->users_count }}</span>
                    </li>
                @endforeach
            </ul>
        </x-common.component-card>

        <x-common.component-card title="Quick actions">
            <div class="flex gap-3">
                <a href="{{ route('admin.users.index') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                    {{ __('Manage Users & Roles') }}
                </a>
                <a href="{{ route('patients.index') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-lg bg-white px-5 py-3.5 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]">
                    {{ __('Patient List') }}
                </a>
            </div>
        </x-common.component-card>
    </div>
</div>
