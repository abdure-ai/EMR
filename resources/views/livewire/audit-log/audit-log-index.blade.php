@php
    $selectClass = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $actionColor = fn ($action) => match ($action) {
        'created' => 'success',
        'updated' => 'warning',
        'deleted' => 'error',
        default => 'light',
    };
    $hiddenKeys = ['id', 'updated_at'];
    $fmt = function ($value) {
        return match (true) {
            is_null($value) => '—',
            is_bool($value) => $value ? 'true' : 'false',
            is_array($value) => json_encode($value),
            default => (string) $value,
        };
    };
@endphp

<div>
    <x-common.page-breadcrumb pageTitle="Audit Log" />

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
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="User name, email, or record ID..."
                               class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-11 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Entity</label>
                    <select wire:model.live="entityType" class="{{ $selectClass }}">
                        <option value="">All</option>
                        @foreach ($entityTypeOptions as $type)
                            <option value="{{ $type }}">{{ \App\Livewire\AuditLog\AuditLogIndex::humanizeEntityType($type) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Action</label>
                    <select wire:model.live="action" class="{{ $selectClass }}">
                        <option value="">All</option>
                        <option value="created">Created</option>
                        <option value="updated">Updated</option>
                        <option value="deleted">Deleted</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">From</label>
                    <input type="date" wire:model.live="from" class="{{ $selectClass }}">
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">To</label>
                    <input type="date" wire:model.live="to" class="{{ $selectClass }}">
                </div>

                @if ($search || $entityType || $action || $from || $to)
                    <button wire:click="clearFilters" type="button"
                            class="h-11 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                        Clear filters
                    </button>
                @endif
            </div>
        </div>

        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $logs->total() }} {{ Str::plural('entry', $logs->total()) }}</p>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="max-w-full overflow-x-auto custom-scrollbar">
                <table class="w-full min-w-[820px]">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">When</p></th>
                            <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">User</p></th>
                            <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Action</p></th>
                            <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Entity</p></th>
                            <th class="px-5 py-3 text-right sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Details</p></th>
                        </tr>
                    </thead>
                        @forelse ($logs as $log)
                            @php
                                $changed = $log->action === 'updated'
                                    ? collect(array_keys($log->after ?? []))->reject(fn ($key) => in_array($key, $hiddenKeys))->values()
                                    : collect(array_keys(($log->action === 'deleted' ? $log->before : $log->after) ?? []))->reject(fn ($key) => in_array($key, $hiddenKeys))->values();
                            @endphp
                            <tbody x-data="{ open: false }">
                            <tr class="border-b border-gray-100 last:border-0 dark:border-gray-800">
                                <td class="px-5 py-4 text-theme-sm text-gray-500 dark:text-gray-400 sm:px-6">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                <td class="px-5 py-4 text-theme-sm text-gray-800 dark:text-white/90 sm:px-6">{{ $log->user?->name ?? 'System' }}</td>
                                <td class="px-5 py-4 sm:px-6"><x-ui.badge variant="solid" :color="$actionColor($log->action)">{{ $log->action }}</x-ui.badge></td>
                                <td class="px-5 py-4 text-theme-sm text-gray-800 dark:text-white/90 sm:px-6">
                                    {{ \App\Livewire\AuditLog\AuditLogIndex::humanizeEntityType($log->entity_type) }}
                                    <span class="text-gray-400">#{{ $log->entity_id }}</span>
                                </td>
                                <td class="px-5 py-4 text-right sm:px-6">
                                    @if ($changed->isNotEmpty())
                                        <button @click="open = !open" type="button" class="text-sm text-brand-500 hover:text-brand-600">
                                            <span x-show="!open">View</span>
                                            <span x-show="open" x-cloak>Hide</span>
                                        </button>
                                    @else
                                        <span class="text-theme-xs text-gray-400">&mdash;</span>
                                    @endif
                                </td>
                            </tr>
                            @if ($changed->isNotEmpty())
                                <tr x-show="open" x-cloak class="border-b border-gray-100 bg-gray-50 last:border-0 dark:border-gray-800 dark:bg-white/[0.02]">
                                    <td colspan="5" class="px-5 py-4 sm:px-6">
                                        <table class="w-full text-theme-xs">
                                            <tbody>
                                                @foreach ($changed as $key)
                                                    <tr class="align-top">
                                                        <td class="w-40 py-1 pr-3 font-medium text-gray-500 dark:text-gray-400">{{ Str::headline($key) }}</td>
                                                        @if ($log->action === 'updated')
                                                            <td class="py-1 pr-3 text-gray-500 line-through dark:text-gray-400">{{ $fmt($log->before[$key] ?? null) }}</td>
                                                            <td class="py-1 text-gray-800 dark:text-white/90">{{ $fmt($log->after[$key] ?? null) }}</td>
                                                        @elseif ($log->action === 'deleted')
                                                            <td colspan="2" class="py-1 text-gray-800 dark:text-white/90">{{ $fmt($log->before[$key] ?? null) }}</td>
                                                        @else
                                                            <td colspan="2" class="py-1 text-gray-800 dark:text-white/90">{{ $fmt($log->after[$key] ?? null) }}</td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            @endif
                            </tbody>
                        @empty
                            <tbody>
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-theme-sm text-gray-500 dark:text-gray-400">No audit entries found.</td>
                            </tr>
                            </tbody>
                        @endforelse
                </table>
            </div>
            <div class="px-5 py-4 sm:px-6">{{ $logs->links() }}</div>
        </div>
    </div>
</div>
