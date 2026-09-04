<div>
<x-common.page-breadcrumb pageTitle="Users & Roles" />

<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by name or email..."
               class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full max-w-sm rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">

        <a href="{{ route('admin.users.create') }}"
           class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
            {{ __('New User') }}
        </a>
    </div>

    @error('self')
        <x-ui.alert variant="error" title="Action not allowed" :message="$message" />
    @enderror

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="max-w-full overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[760px]">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Name</p></th>
                        <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Email</p></th>
                        <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Role</p></th>
                        <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Status</p></th>
                        <th class="px-5 py-3 sm:px-6"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr class="border-b border-gray-100 last:border-0 dark:border-gray-800">
                            <td class="px-5 py-4 text-theme-sm text-gray-800 dark:text-white/90 sm:px-6">{{ $user->name }}</td>
                            <td class="px-5 py-4 text-theme-sm text-gray-500 dark:text-gray-400 sm:px-6">{{ $user->email }}</td>
                            <td class="px-5 py-4 text-theme-sm text-gray-800 dark:text-white/90 sm:px-6">
                                {{ $user->roles->pluck('name')->join(', ') ?: '— none —' }}
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <x-ui.badge variant="solid" :color="$user->is_active ? 'success' : 'error'">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </x-ui.badge>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex items-center justify-end gap-3">
                                    @can('update', $user)
                                        <a href="{{ route('admin.users.edit', $user) }}" title="{{ __('Edit') }}"
                                           class="text-gray-400 hover:text-brand-500">
                                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M13.89 2.86a1.75 1.75 0 0 1 2.475 2.475L15.06 6.64l-2.475-2.475 1.305-1.305Zm-2.366 2.366L3.146 13.604a1 1 0 0 0-.263.464l-.7 3.03a.5.5 0 0 0 .6.6l3.03-.7a1 1 0 0 0 .464-.263l8.378-8.378-2.475-2.475Z" fill="currentColor"/>
                                            </svg>
                                        </a>
                                    @endcan
                                    <button wire:click="toggleActive({{ $user->id }})" wire:confirm="Are you sure?"
                                            class="text-theme-sm text-gray-600 hover:underline dark:text-gray-300">
                                        {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 sm:px-6">{{ $users->links() }}</div>
    </div>
</div>
</div>
