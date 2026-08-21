<div>
    <x-common.page-breadcrumb pageTitle="Practitioner" />

    <div class="space-y-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 md:gap-6">
            <x-stat-card label="Patients in system" :value="$totalPatients" />
            <x-stat-card label="Appointments today" :value="$appointmentsToday" />
            <x-stat-card label="Waiting for me" :value="$myQueue->count()" />
        </div>

        <x-common.component-card title="My queue">
            @if ($myQueue->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No one is waiting right now.</p>
            @else
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($myQueue as $entry)
                        <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                            <div class="flex items-center gap-3">
                                <span class="font-mono text-sm text-gray-500 dark:text-gray-400">{{ $entry->token_number }}</span>
                                <a href="{{ route('patients.show', $entry->patient) }}" class="text-sm text-gray-800 hover:text-brand-500 dark:text-white/90">
                                    {{ $entry->patient->full_name }}
                                </a>
                                <x-ui.badge variant="solid" :color="$entry->status === 'with_practitioner' ? 'warning' : 'light'" size="sm">
                                    {{ $entry->status === 'with_practitioner' ? 'With you' : 'Waiting' }}
                                </x-ui.badge>
                            </div>
                            <div class="flex items-center gap-3">
                                @if ($entry->status === 'waiting')
                                    <button wire:click="start({{ $entry->id }})"
                                            class="text-sm font-medium text-brand-500 hover:text-brand-600">
                                        Start
                                    </button>
                                @else
                                    <a href="{{ route('patients.show', $entry->patient) }}" class="text-sm font-medium text-brand-500 hover:text-brand-600">
                                        Document
                                    </a>
                                    <button wire:click="complete({{ $entry->id }})"
                                            class="text-sm font-medium text-success-500 hover:text-success-600">
                                        Complete
                                    </button>
                                @endif
                                <button wire:click="cancel({{ $entry->id }})" wire:confirm="Remove this patient from your queue?"
                                        class="text-sm text-gray-400 hover:text-error-500">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-common.component-card>

        <x-common.component-card title="Quick actions">
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('appointments.index') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                    {{ __('My Appointments') }}
                </a>
                <a href="{{ route('patients.index') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-lg bg-white px-5 py-3.5 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]">
                    {{ __('Patient List') }}
                </a>
            </div>
        </x-common.component-card>
    </div>
</div>
