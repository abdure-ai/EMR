@php
    $inputClass = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $textareaClass = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
@endphp

<div>
    <x-common.page-breadcrumb pageTitle="Edit Post" />

    @if (session('status'))
        <div class="mb-4">
            <x-ui.alert variant="success" title="Saved" :message="session('status')" />
        </div>
    @endif

    <x-common.component-card title="Edit News Post">
        <form wire:submit="save" class="max-w-2xl space-y-5">
            <div>
                <label for="title" class="{{ $labelClass }}">Title</label>
                <input id="title" wire:model="title" type="text" class="{{ $inputClass }}">
                <x-input-error :messages="$errors->get('title')" class="mt-2" />
            </div>

            <div>
                <label for="excerpt" class="{{ $labelClass }}">Excerpt (optional)</label>
                <input id="excerpt" wire:model="excerpt" type="text" class="{{ $inputClass }}">
                <x-input-error :messages="$errors->get('excerpt')" class="mt-2" />
            </div>

            <div>
                <label for="body" class="{{ $labelClass }}">Body</label>
                <textarea id="body" wire:model="body" rows="10" class="{{ $textareaClass }}"></textarea>
                <x-input-error :messages="$errors->get('body')" class="mt-2" />
            </div>

            <div>
                <label class="{{ $labelClass }}">Featured image</label>
                <div class="flex items-start gap-4">
                    <div class="h-28 w-44 shrink-0 overflow-hidden rounded-lg border border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/5">
                        @if ($image)
                            <img src="{{ $image->temporaryUrl() }}" class="h-full w-full object-cover" alt="">
                        @elseif ($post->image_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($post->image_path) }}" class="h-full w-full object-cover" alt="">
                        @else
                            <div class="flex h-full items-center justify-center text-xs text-gray-400">No image</div>
                        @endif
                    </div>
                    <div class="space-y-2">
                        <input type="file" wire:model="image" accept="image/*" class="block text-sm text-gray-600 dark:text-gray-400">
                        <div wire:loading wire:target="image" class="text-xs text-gray-400">Uploading…</div>
                        @if ($post->image_path && ! $image)
                            <button type="button" wire:click="removeImage" wire:confirm="Remove this image?" class="text-xs text-error-500 hover:text-error-600">Remove image</button>
                        @endif
                        <x-input-error :messages="$errors->get('image')" class="mt-2" />
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input id="is_published" wire:model="is_published" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                <label for="is_published" class="text-sm text-gray-700 dark:text-gray-300">Published</label>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.news.index') }}" class="self-center text-sm text-gray-600 dark:text-gray-300">Back</a>
                <x-ui.button type="submit">{{ __('Save Changes') }}</x-ui.button>
            </div>
        </form>
    </x-common.component-card>
</div>
