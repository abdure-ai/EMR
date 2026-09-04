@php
    $selectClass = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
@endphp

<div>
    <x-common.page-breadcrumb pageTitle="News" />

    <div class="space-y-4">
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex flex-wrap items-end gap-3">
                <div class="min-w-[220px] flex-1">
                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Search</label>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Post title..."
                           class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Status</label>
                    <select wire:model.live="status" class="{{ $selectClass }}">
                        <option value="">All</option>
                        <option value="published">Published</option>
                        <option value="draft">Draft</option>
                    </select>
                </div>

                @if ($search || $status)
                    <button wire:click="clearFilters" type="button"
                            class="h-11 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                        Clear filters
                    </button>
                @endif

                <div class="ml-auto">
                    <a href="{{ route('admin.news.create') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                        {{ __('New Post') }}
                    </a>
                </div>
            </div>
        </div>

        @if (session('status'))
            <x-ui.alert variant="success" title="Done" :message="session('status')" />
        @endif

        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $posts->total() }} {{ Str::plural('post', $posts->total()) }}</p>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="max-w-full overflow-x-auto custom-scrollbar">
                <table class="w-full min-w-[720px]">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Title</p></th>
                            <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Status</p></th>
                            <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Published</p></th>
                            <th class="px-5 py-3 text-right sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Actions</p></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($posts as $post)
                            <tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4 text-theme-sm text-gray-800 dark:text-white/90 sm:px-6">{{ $post->title }}</td>
                                <td class="px-5 py-4 sm:px-6">
                                    <x-ui.badge variant="solid" :color="$post->is_published ? 'success' : 'light'">
                                        {{ $post->is_published ? 'Published' : 'Draft' }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-5 py-4 text-theme-sm text-gray-500 dark:text-gray-400 sm:px-6">{{ $post->published_at?->format('Y-m-d') ?? '—' }}</td>
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex items-center justify-end gap-4 text-theme-sm">
                                        <button wire:click="togglePublished({{ $post->id }})" type="button" class="text-gray-500 hover:text-brand-500 dark:text-gray-400">
                                            {{ $post->is_published ? 'Unpublish' : 'Publish' }}
                                        </button>
                                        <a href="{{ route('admin.news.edit', $post) }}" class="text-gray-500 hover:text-brand-500 dark:text-gray-400">Edit</a>
                                        <button wire:click="delete({{ $post->id }})" wire:confirm="Delete \"{{ $post->title }}\"?" type="button" class="text-gray-500 hover:text-error-500 dark:text-gray-400">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-theme-sm text-gray-500 dark:text-gray-400">No posts yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 sm:px-6">{{ $posts->links() }}</div>
        </div>
    </div>
</div>
