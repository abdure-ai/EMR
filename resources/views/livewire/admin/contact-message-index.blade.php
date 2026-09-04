@php
    $selectClass = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
@endphp

<div>
    <x-common.page-breadcrumb pageTitle="Contact Messages" />

    <div class="space-y-4">
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Status</label>
                    <select wire:model.live="status" class="{{ $selectClass }}">
                        <option value="">All</option>
                        <option value="unread">Unread</option>
                        <option value="read">Read</option>
                    </select>
                </div>
                @if ($unreadCount > 0)
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $unreadCount }} unread</span>
                @endif
            </div>
        </div>

        @if (session('status'))
            <x-ui.alert variant="success" title="Done" :message="session('status')" />
        @endif

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="max-w-full overflow-x-auto custom-scrollbar">
                <table class="w-full min-w-[720px]">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">From</p></th>
                            <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Subject</p></th>
                            <th class="px-5 py-3 text-left sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Received</p></th>
                            <th class="px-5 py-3 text-right sm:px-6"><p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Actions</p></th>
                        </tr>
                    </thead>
                    @forelse ($messages as $message)
                        <tbody x-data="{ open: false }">
                        <tr class="border-b border-gray-100 last:border-0 dark:border-gray-800 {{ $message->is_read ? '' : 'bg-brand-50/40 dark:bg-brand-500/5' }}">
                            <td class="px-5 py-4 text-theme-sm text-gray-800 dark:text-white/90 sm:px-6">
                                @unless ($message->is_read)
                                    <span class="mr-1.5 inline-block h-2 w-2 rounded-full bg-brand-500"></span>
                                @endunless
                                {{ $message->name }}
                                <div class="text-theme-xs text-gray-400">{{ $message->email }}</div>
                            </td>
                            <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-300 sm:px-6">{{ $message->subject ?: '—' }}</td>
                            <td class="px-5 py-4 text-theme-sm text-gray-500 dark:text-gray-400 sm:px-6">{{ $message->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-5 py-4 text-right sm:px-6">
                                <div class="flex items-center justify-end gap-4 text-theme-sm">
                                    <button @click="open = !open" type="button" wire:click="markRead({{ $message->id }})" class="text-brand-500 hover:text-brand-600">
                                        <span x-show="!open">View</span>
                                        <span x-show="open" x-cloak>Hide</span>
                                    </button>
                                    <button wire:click="delete({{ $message->id }})" wire:confirm="Delete this message?" type="button" class="text-gray-500 hover:text-error-500 dark:text-gray-400">Delete</button>
                                </div>
                            </td>
                        </tr>
                        <tr x-show="open" x-cloak class="border-b border-gray-100 bg-gray-50 last:border-0 dark:border-gray-800 dark:bg-white/[0.02]">
                            <td colspan="4" class="px-5 py-4 sm:px-6">
                                <p class="whitespace-pre-line text-theme-sm text-gray-700 dark:text-gray-300">{{ $message->message }}</p>
                                @if ($message->phone)
                                    <p class="mt-2 text-theme-xs text-gray-400">Phone: {{ $message->phone }}</p>
                                @endif
                            </td>
                        </tr>
                        </tbody>
                    @empty
                        <tbody>
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-theme-sm text-gray-500 dark:text-gray-400">No messages yet.</td>
                        </tr>
                        </tbody>
                    @endforelse
                </table>
            </div>
            <div class="px-5 py-4 sm:px-6">{{ $messages->links() }}</div>
        </div>
    </div>
</div>
