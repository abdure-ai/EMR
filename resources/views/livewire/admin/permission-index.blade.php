<div>
    <x-common.page-breadcrumb pageTitle="Permissions" />

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
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Permission name..."
                               class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-11 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                    </div>
                </div>

                @if ($search)
                    <button wire:click="clearFilters" type="button"
                            class="h-11 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                        Clear filters
                    </button>
                @endif

                <div class="ml-auto">
                    @can('create', \Spatie\Permission\Models\Permission::class)
                        <a href="{{ route('admin.permissions.create') }}"
                           class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                            {{ __('New Permission') }}
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        @if (session('status'))
            <x-ui.alert variant="success" title="Done" :message="session('status')" />
        @endif

        @if ($permissionGroups->isEmpty())
            <div class="rounded-xl border border-gray-200 bg-white p-8 text-center text-sm text-gray-500 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400">
                No permissions found.
            </div>
        @endif

        <div class="space-y-4">
            @foreach ($permissionGroups as $group => $permissions)
                <x-common.component-card :title="ucfirst($group)">
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($permissions as $permission)
                            <div class="flex items-center justify-between py-2 first:pt-0 last:pb-0">
                                <div>
                                    <p class="text-sm text-gray-800 dark:text-white/90">{{ $permission->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $permission->roles_count }} role(s)</p>
                                </div>
                                @can('delete', $permission)
                                    <button wire:click="delete({{ $permission->id }})"
                                            wire:confirm="Delete this permission? Roles that grant it will lose it, and any code checking for it will start denying access."
                                            class="text-sm text-gray-500 hover:text-error-500 dark:text-gray-400">
                                        Delete
                                    </button>
                                @endcan
                            </div>
                        @endforeach
                    </div>
                </x-common.component-card>
            @endforeach
        </div>
    </div>
</div>
