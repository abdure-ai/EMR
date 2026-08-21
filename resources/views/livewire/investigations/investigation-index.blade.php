@php
    $selectClass = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $categoryColors = ['lab' => 'info', 'imaging' => 'primary', 'procedure' => 'warning'];
@endphp

<div>
    <x-common.page-breadcrumb pageTitle="Investigations" />

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
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Name or subcategory..."
                               class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-11 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Category</label>
                    <select wire:model.live="category" class="{{ $selectClass }}">
                        <option value="">All</option>
                        <option value="lab">Lab</option>
                        <option value="imaging">Imaging</option>
                        <option value="procedure">Procedure</option>
                    </select>
                </div>

                @if ($search || $category)
                    <button wire:click="clearFilters" type="button"
                            class="h-11 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                        Clear filters
                    </button>
                @endif

                <div class="ml-auto">
                    @can('create', \App\Models\Investigation::class)
                        <a href="{{ route('investigations.create') }}"
                           class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                            {{ __('New Investigation') }}
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        @if (session('status'))
            <x-ui.alert variant="success" title="Done" :message="session('status')" />
        @endif

        <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ $investigations->total() }} {{ Str::plural('investigation', $investigations->total()) }} found
        </p>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="max-w-full overflow-x-auto custom-scrollbar">
                <table class="w-full min-w-[760px]">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Category</p></th>
                            <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Subcategory</p></th>
                            <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Name</p></th>
                            <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Price</p></th>
                            <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Status</p></th>
                            <th class="px-5 py-3 sm:px-6"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($investigations as $investigation)
                            <tr class="border-b border-gray-100 last:border-0 dark:border-gray-800">
                                <td class="px-5 py-4 sm:px-6">
                                    <x-ui.badge size="sm" variant="solid" :color="$categoryColors[$investigation->category] ?? 'light'">
                                        {{ ucfirst($investigation->category) }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-5 py-4 text-theme-sm text-gray-500 dark:text-gray-400 sm:px-6">{{ $investigation->subcategory ?: '—' }}</td>
                                <td class="px-5 py-4 text-theme-sm text-gray-800 dark:text-white/90 sm:px-6">{{ $investigation->name }}</td>
                                <td class="px-5 py-4 text-theme-sm text-gray-500 dark:text-gray-400 sm:px-6">{{ $investigation->price !== null ? number_format($investigation->price, 2).' ETB' : '—' }}</td>
                                <td class="px-5 py-4 sm:px-6">
                                    <x-ui.badge variant="solid" :color="$investigation->is_active ? 'success' : 'light'">
                                        {{ $investigation->is_active ? 'Active' : 'Inactive' }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-5 py-4 text-right text-theme-sm sm:px-6">
                                    @can('update', $investigation)
                                        <a href="{{ route('investigations.edit', $investigation) }}" class="text-brand-500 hover:text-brand-600">Edit</a>
                                        <button wire:click="toggleActive({{ $investigation->id }})" class="ml-3 text-gray-500 hover:text-gray-700 dark:text-gray-400">
                                            {{ $investigation->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-theme-sm text-gray-500 dark:text-gray-400">No investigations found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 sm:px-6">{{ $investigations->links() }}</div>
        </div>
    </div>
</div>
