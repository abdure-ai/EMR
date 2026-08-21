<div>
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div class="flex items-center gap-4">
        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-brand-500 text-lg font-semibold text-white">
            {{ strtoupper(substr($patient->first_name, 0, 1).substr($patient->last_name, 0, 1)) }}
        </span>
        <div>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                {{ $patient->full_name }}
                <span class="ms-2 font-mono text-sm text-gray-400">{{ $patient->patient_id }}</span>
            </h2>
            <div class="mt-1 flex items-center gap-2">
                <x-ui.badge size="sm" color="light">{{ ucfirst($patient->sex) }}</x-ui.badge>
                <x-ui.badge size="sm" color="light">
                    {{ $patient->date_of_birth?->age ?? $patient->age ?? '—' }} yrs
                </x-ui.badge>
            </div>
        </div>
    </div>
    <div class="flex items-center gap-4">
        @if ($canCheckIn)
            <a href="{{ route('patients.check-in', $patient) }}" class="text-sm font-medium text-brand-500 hover:text-brand-600">Check In</a>
        @endif
        @can('update', $patient)
            <a href="{{ route('patients.edit', $patient) }}" class="text-sm font-medium text-brand-500 hover:text-brand-600">Edit patient</a>
        @endcan
        <a href="{{ route('patients.index') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">&larr; Back to list</a>
    </div>
</div>

<div class="max-w-4xl space-y-6">
    @if (session('status'))
        <x-ui.alert variant="success" title="Saved" :message="session('status')" />
    @endif

    <x-common.component-card title="Demographics">
        <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
            <div><dt class="text-gray-500 dark:text-gray-400">Gender</dt><dd class="capitalize text-gray-800 dark:text-white/90">{{ $patient->sex }}</dd></div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400">Age</dt>
                <dd class="text-gray-800 dark:text-white/90">
                    @if ($patient->date_of_birth)
                        {{ $patient->date_of_birth->age }} yrs ({{ $patient->date_of_birth->format('Y-m-d') }})
                    @elseif ($patient->age !== null)
                        {{ $patient->age }} yrs
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div><dt class="text-gray-500 dark:text-gray-400">Phone</dt><dd class="text-gray-800 dark:text-white/90">{{ $patient->phone }}</dd></div>
            <div><dt class="text-gray-500 dark:text-gray-400">Preferred language</dt><dd class="text-gray-800 dark:text-white/90">{{ strtoupper($patient->preferred_language) }}</dd></div>
            <div class="sm:col-span-2">
                <dt class="text-gray-500 dark:text-gray-400">Address</dt>
                <dd class="text-gray-800 dark:text-white/90">
                    {{ $patient->full_address ?: '—' }}
                    @if ($patient->house_no)
                        , House {{ $patient->house_no }}
                    @endif
                </dd>
            </div>
            <div><dt class="text-gray-500 dark:text-gray-400">Emergency contact</dt><dd class="text-gray-800 dark:text-white/90">{{ $patient->emergency_contact_name ?: '—' }} {{ $patient->emergency_contact_phone }}</dd></div>
            <div><dt class="text-gray-500 dark:text-gray-400">ID document ref</dt><dd class="text-gray-800 dark:text-white/90">{{ $patient->id_document_ref ?: '—' }}</dd></div>
            <div><dt class="text-gray-500 dark:text-gray-400">Registered</dt><dd class="text-gray-800 dark:text-white/90">{{ $patient->created_at->format('Y-m-d') }} by {{ $patient->creator?->name ?? '—' }}</dd></div>
        </dl>
    </x-common.component-card>

    <x-common.component-card title="Encounters">
        <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                        <th class="px-4 py-2.5 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Time</th>
                        <th class="px-4 py-2.5 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Practitioner</th>
                        <th class="px-4 py-2.5 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-4 py-2.5 text-right text-theme-xs font-medium text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($encounters as $encounter)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $encounter->created_at->format('Y-m-d, H:i') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $encounter->practitioner->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <x-ui.badge size="sm" variant="solid" :color="$encounter->status === 'completed' ? 'success' : 'warning'">
                                    {{ str($encounter->status)->headline() }}
                                </x-ui.badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if ($canViewMedical)
                                    <a href="{{ route('encounters.show', [$patient, $encounter]) }}" title="View"
                                       class="inline-flex text-gray-400 hover:text-brand-500">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M10 4.375c-4.66 0-7.836 3.9-8.62 5.02a.75.75 0 0 0 0 .81c.784 1.12 3.96 5.02 8.62 5.02s7.836-3.9 8.62-5.02a.75.75 0 0 0 0-.81C17.836 8.275 14.66 4.375 10 4.375Zm0 8.75a3.125 3.125 0 1 1 0-6.25 3.125 3.125 0 0 1 0 6.25Z" fill="currentColor"/>
                                        </svg>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-400">No encounters yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-common.component-card>
</div>
</div>
