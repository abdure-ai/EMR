@php
    $inputClass = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $textareaClass = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
@endphp

<div>
    <x-common.page-breadcrumb pageTitle="New Post" />

    <x-common.component-card title="New News Post">
        <form wire:submit="save" class="max-w-2xl space-y-5">
            <div>
                <label for="title" class="{{ $labelClass }}">Title</label>
                <input id="title" wire:model="title" type="text" class="{{ $inputClass }}">
                <x-input-error :messages="$errors->get('title')" class="mt-2" />
            </div>

            <div>
                <label for="excerpt" class="{{ $labelClass }}">Excerpt (optional)</label>
                <input id="excerpt" wire:model="excerpt" type="text" placeholder="Shown on the News list and link previews" class="{{ $inputClass }}">
                <x-input-error :messages="$errors->get('excerpt')" class="mt-2" />
            </div>

            <div>
                <label for="body" class="{{ $labelClass }}">Body</label>
                <textarea id="body" wire:model="body" rows="10" class="{{ $textareaClass }}"></textarea>
                <p class="mt-1 text-xs text-gray-400">Plain text - blank lines become paragraph breaks on the page.</p>
                <x-input-error :messages="$errors->get('body')" class="mt-2" />
            </div>

            <div>
                <label class="{{ $labelClass }}">Featured image (optional)</label>
                @if ($image)
                    <img src="{{ $image->temporaryUrl() }}" class="mb-2 h-40 w-full max-w-sm rounded-lg object-cover" alt="">
                @endif
                <input type="file" wire:model="image" accept="image/*" class="block text-sm text-gray-600 dark:text-gray-400">
                <div wire:loading wire:target="image" class="text-xs text-gray-400">Uploading…</div>
                <x-input-error :messages="$errors->get('image')" class="mt-2" />
            </div>

            <div class="flex items-center gap-2">
                <input id="is_published" wire:model="is_published" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                <label for="is_published" class="text-sm text-gray-700 dark:text-gray-300">Publish immediately</label>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.news.index') }}" class="self-center text-sm text-gray-600 dark:text-gray-300">Cancel</a>
                <x-ui.button type="submit">{{ __('Create Post') }}</x-ui.button>
            </div>
        </form>
    </x-common.component-card>
</div>
