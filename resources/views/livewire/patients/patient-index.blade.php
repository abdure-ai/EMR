@php
    $selectClass = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
@endphp

<div>
    <x-common.page-breadcrumb pageTitle="Patients" />

    <div class="space-y-4">
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex flex-wrap items-end gap-3">
                <div class="min-w-[220px] flex-1">
                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Search</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04175 9.37363C3.04175 5.87693 5.87711 3.04199 9.37508 3.04199C12.8731 3.04199 15.7084 5.87693 15.7084 9.37363C15.7084 12.8703 12.8731 15.7053 9.37508 15.7053C5.87711 15.7053 3.04175 12.8703 3.04175 9.37363ZM9.37508 1.54199C5.04902 1.54199 1.54175 5.04817 1.54175 9.37363C1.54175 13.6991 5.04902 17.2053 9.37508 17.2053C11.2674 17.2053 13.003 16.5344 14.357 15.4176L17.177 18.238C17.4699 18.5309 17.9448 18.5309 18.2377 18.238C18.5306 17.9451 18.5306 17.4703 18.2377 17.1774L15.418 14.3573C16.5365 13.0033 17.2084 11.2669 17.2084 9.37363C17.2084 5.04817 13.7011 1.54199 9.37508 1.54199Z" fill="currentColor" />
                            </svg>
                        </span>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Patient ID, name, or phone..."
                               class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-11 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Gender</label>
                    <select wire:model.live="gender" class="{{ $selectClass }}">
                        <option value="">All</option>
                        <option value="female">Female</option>
                        <option value="male">Male</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Region</label>
                    <select wire:model.live="region" class="{{ $selectClass }}">
                        <option value="">All</option>
                        @foreach ($regionOptions as $option)
                            <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Sort by</label>
                    <select wire:model.live="sort" class="{{ $selectClass }}">
                        <option value="newest">Newest first</option>
                        <option value="oldest">Oldest first</option>
                        <option value="name_asc">Name A&ndash;Z</option>
                        <option value="name_desc">Name Z&ndash;A</option>
                    </select>
                </div>

                @unless (auth()->user()->hasRole('Practitioner'))
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Show</label>
                        <select wire:model.live="scope" class="{{ $selectClass }}">
                            <option value="today">Today only</option>
                            <option value="all">All patients</option>
                        </select>
                    </div>
                @endunless

                @if ($search || $gender || $region || $sort !== 'newest' || (! auth()->user()->hasRole('Practitioner') && $scope !== 'today'))
                    <button wire:click="clearFilters" type="button"
                            class="h-11 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                        Clear filters
                    </button>
                @endif

                <div class="ml-auto">
                    @can('create', \App\Models\Patient::class)
                        <a href="{{ route('patients.create') }}"
                           class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                            {{ __('Register Patient') }}
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        @if (session('status'))
            <x-ui.alert variant="success" title="Done" :message="session('status')" />
        @endif

        <p class="text-sm text-gray-500 dark:text-gray-400">
            @if (auth()->user()->hasRole('Practitioner'))
                {{ $patients->total() }} {{ Str::plural('patient', $patients->total()) }} checked in and assigned to you
            @else
                {{ $patients->total() }} {{ Str::plural('patient', $patients->total()) }}
                {{ $scope === 'today' ? 'registered today' : 'found' }}
                @if ($scope === 'today')
                    &middot; <button wire:click="$set('scope', 'all')" type="button" class="text-brand-500 hover:text-brand-600">Show all patients</button>
                @endif
            @endif
        </p>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="max-w-full overflow-x-auto custom-scrollbar">
                <table class="w-full min-w-[820px]">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Patient ID</p></th>
                            <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Name</p></th>
                            <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Gender</p></th>
                            <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Phone</p></th>
                            <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Region</p></th>
                            <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Registered</p></th>
                            <th class="px-5 py-3 text-right sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Actions</p></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($patients as $patient)
                            @php
                                // Same rule as the appointments list: a practitioner can't
                                // identify a patient the cashier hasn't confirmed payment for
                                // yet - registration fee included. But once the patient has
                                // actually been queued at least once, a later unrelated pending
                                // invoice must not re-mask someone already under their care.
                                $identityHidden = auth()->user()->hasRole('Practitioner')
                                    && $patient->has_pending_invoice
                                    && ! $patient->queue_entries_exists;
                            @endphp
                            <tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4 font-mono text-theme-sm text-gray-800 dark:text-white/90 sm:px-6">{{ $patient->patient_id }}</td>
                                <td class="px-5 py-4 text-theme-sm text-gray-800 dark:text-white/90 sm:px-6">
                                    @if ($identityHidden)
                                        <span class="italic text-gray-400">Pending cashier confirmation</span>
                                    @else
                                        {{ $patient->full_name }}
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-theme-sm capitalize text-gray-500 dark:text-gray-400 sm:px-6">{{ $patient->sex }}</td>
                                <td class="px-5 py-4 text-theme-sm text-gray-500 dark:text-gray-400 sm:px-6">{{ $identityHidden ? '—' : $patient->phone }}</td>
                                <td class="px-5 py-4 text-theme-sm text-gray-500 dark:text-gray-400 sm:px-6">{{ $patient->region ?: '—' }}</td>
                                <td class="px-5 py-4 text-theme-sm text-gray-500 dark:text-gray-400 sm:px-6">{{ $patient->created_at->format('Y-m-d') }}</td>
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex items-center justify-end gap-3">
                                        @unless ($identityHidden)
                                        <a href="{{ route('patients.show', $patient) }}" title="View"
                                           class="text-gray-400 hover:text-brand-500">
                                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M10 4.375c-4.66 0-7.836 3.9-8.62 5.02a.75.75 0 0 0 0 .81c.784 1.12 3.96 5.02 8.62 5.02s7.836-3.9 8.62-5.02a.75.75 0 0 0 0-.81C17.836 8.275 14.66 4.375 10 4.375Zm0 8.75a3.125 3.125 0 1 1 0-6.25 3.125 3.125 0 0 1 0 6.25Z" fill="currentColor"/>
                                            </svg>
                                        </a>
                                        @endunless
                                        @can('create', \App\Models\QueueEntry::class)
                                            @if (! $patient->has_active_queue_entry && ! $patient->has_pending_visit_invoice)
                                                <a href="{{ route('patients.check-in', $patient) }}" title="Check In"
                                                   class="text-gray-400 hover:text-brand-500">
                                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M10 2.5a7.5 7.5 0 1 0 0 15 7.5 7.5 0 0 0 0-15ZM1 10a9 9 0 1 1 18 0 9 9 0 0 1-18 0Zm12.53-2.03a.75.75 0 0 1 0 1.06l-4 4a.75.75 0 0 1-1.06 0l-2-2a.75.75 0 1 1 1.06-1.06l1.47 1.47 3.47-3.47a.75.75 0 0 1 1.06 0Z" fill="currentColor"/>
                                                    </svg>
                                                </a>
                                            @endif
                                        @endcan
                                        @can('update', $patient)
                                            <a href="{{ route('patients.edit', $patient) }}" title="Edit"
                                               class="text-gray-400 hover:text-brand-500">
                                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M13.89 2.86a1.75 1.75 0 0 1 2.475 2.475L15.06 6.64l-2.475-2.475 1.305-1.305Zm-2.366 2.366L3.146 13.604a1 1 0 0 0-.263.464l-.7 3.03a.5.5 0 0 0 .6.6l3.03-.7a1 1 0 0 0 .464-.263l8.378-8.378-2.475-2.475Z" fill="currentColor"/>
                                                </svg>
                                            </a>
                                        @endcan
                                        @can('delete', $patient)
                                            <button wire:click="delete({{ $patient->id }})"
                                                    wire:confirm="Archive {{ $patient->full_name }}? This removes them from the active patient list. This isn't a permanent delete - the record is preserved."
                                                    title="Delete" class="text-gray-400 hover:text-error-500">
                                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M8.125 2.5a.625.625 0 0 0-.625.625V3.75h5v-.625a.625.625 0 0 0-.625-.625h-3.75Zm5.625 1.25v-.625A1.875 1.875 0 0 0 11.875 1.25h-3.75A1.875 1.875 0 0 0 6.25 3.125v.625H3.125a.625.625 0 1 0 0 1.25h.696l.633 9.482a1.875 1.875 0 0 0 1.87 1.768h5.352a1.875 1.875 0 0 0 1.87-1.768l.633-9.482h.696a.625.625 0 1 0 0-1.25H13.75ZM8.125 7.5a.625.625 0 0 0-1.25 0v5a.625.625 0 1 0 1.25 0v-5Zm3.75 0a.625.625 0 1 0-1.25 0v5a.625.625 0 1 0 1.25 0v-5Z" fill="currentColor"/>
                                                </svg>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-8 text-center text-theme-sm text-gray-500 dark:text-gray-400">
                                    @if (auth()->user()->hasRole('Practitioner'))
                                        No patients currently checked in and assigned to you.
                                    @elseif ($scope === 'today' && ! $search && ! $gender && ! $region)
                                        No patients registered today yet.
                                        <button wire:click="$set('scope', 'all')" type="button" class="text-brand-500 hover:text-brand-600">Show all patients</button>
                                    @else
                                        No patients found.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 sm:px-6">{{ $patients->links() }}</div>
        </div>
    </div>
</div>
