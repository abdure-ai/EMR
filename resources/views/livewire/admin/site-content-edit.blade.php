@php
    $inputClass = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $textareaClass = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
@endphp

<div>
    <x-common.page-breadcrumb pageTitle="Website Content" />

    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Edits appear live on <a href="{{ route('site.home') }}" target="_blank" class="text-brand-500 hover:text-brand-600">the public website</a> as soon as you save.
        </p>
    </div>

    @if (session('status'))
        <div class="mb-4">
            <x-ui.alert variant="success" title="Saved" :message="session('status')" />
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <x-common.component-card title="Home page hero" desc="The first thing a visitor sees.">
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <div class="space-y-5">
                    <div>
                        <label for="hero_headline" class="{{ $labelClass }}">Headline</label>
                        <input id="hero_headline" wire:model="hero_headline" type="text" class="{{ $inputClass }}">
                        <x-input-error :messages="$errors->get('hero_headline')" class="mt-2" />
                    </div>
                    <div>
                        <label for="hero_subheadline" class="{{ $labelClass }}">Subheadline</label>
                        <textarea id="hero_subheadline" wire:model="hero_subheadline" rows="3" class="{{ $textareaClass }}"></textarea>
                        <x-input-error :messages="$errors->get('hero_subheadline')" class="mt-2" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="hero_cta_label" class="{{ $labelClass }}">Button text</label>
                            <input id="hero_cta_label" wire:model="hero_cta_label" type="text" placeholder="Book an Appointment" class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label for="hero_cta_url" class="{{ $labelClass }}">Button link</label>
                            <input id="hero_cta_url" wire:model="hero_cta_url" type="text" placeholder="/contact" class="{{ $inputClass }}">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Hero image</label>
                    <div class="flex items-start gap-4">
                        <div class="h-32 w-48 shrink-0 overflow-hidden rounded-lg border border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/5">
                            @if ($hero_image)
                                <img src="{{ $hero_image->temporaryUrl() }}" class="h-full w-full object-cover" alt="">
                            @elseif ($content->hero_image_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($content->hero_image_path) }}" class="h-full w-full object-cover" alt="">
                            @else
                                <div class="flex h-full items-center justify-center text-xs text-gray-400">No image yet</div>
                            @endif
                        </div>
                        <div class="space-y-2">
                            <input type="file" wire:model="hero_image" accept="image/*" class="block text-sm text-gray-600 dark:text-gray-400">
                            <div wire:loading wire:target="hero_image" class="text-xs text-gray-400">Uploading…</div>
                            @if ($content->hero_image_path && ! $hero_image)
                                <button type="button" wire:click="removeHeroImage" wire:confirm="Remove the hero image?" class="text-xs text-error-500 hover:text-error-600">Remove image</button>
                            @endif
                            <x-input-error :messages="$errors->get('hero_image')" class="mt-2" />
                        </div>
                    </div>
                </div>
            </div>
        </x-common.component-card>

        <x-common.component-card title="Home page highlights" desc="A short 'why choose us' section below the hero.">
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <div>
                    <label for="home_highlights_heading" class="{{ $labelClass }}">Heading</label>
                    <input id="home_highlights_heading" wire:model="home_highlights_heading" type="text" placeholder="Why Families Trust Nesiha" class="{{ $inputClass }}">
                </div>
                <div>
                    <label for="home_highlights_body" class="{{ $labelClass }}">Body</label>
                    <textarea id="home_highlights_body" wire:model="home_highlights_body" rows="3" class="{{ $textareaClass }}"></textarea>
                </div>
            </div>
        </x-common.component-card>

        <x-common.component-card title="About Us page" desc="Your clinic's story and mission.">
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <div class="space-y-5">
                    <div>
                        <label for="about_heading" class="{{ $labelClass }}">Heading</label>
                        <input id="about_heading" wire:model="about_heading" type="text" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label for="about_body" class="{{ $labelClass }}">Story / mission</label>
                        <textarea id="about_body" wire:model="about_body" rows="8" class="{{ $textareaClass }}"></textarea>
                        <p class="mt-1 text-xs text-gray-400">Plain text - blank lines become paragraph breaks on the page.</p>
                        <x-input-error :messages="$errors->get('about_body')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <label class="{{ $labelClass }}">About image</label>
                    <div class="flex items-start gap-4">
                        <div class="h-32 w-48 shrink-0 overflow-hidden rounded-lg border border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/5">
                            @if ($about_image)
                                <img src="{{ $about_image->temporaryUrl() }}" class="h-full w-full object-cover" alt="">
                            @elseif ($content->about_image_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($content->about_image_path) }}" class="h-full w-full object-cover" alt="">
                            @else
                                <div class="flex h-full items-center justify-center text-xs text-gray-400">No image yet</div>
                            @endif
                        </div>
                        <div class="space-y-2">
                            <input type="file" wire:model="about_image" accept="image/*" class="block text-sm text-gray-600 dark:text-gray-400">
                            <div wire:loading wire:target="about_image" class="text-xs text-gray-400">Uploading…</div>
                            @if ($content->about_image_path && ! $about_image)
                                <button type="button" wire:click="removeAboutImage" wire:confirm="Remove the about image?" class="text-xs text-error-500 hover:text-error-600">Remove image</button>
                            @endif
                            <x-input-error :messages="$errors->get('about_image')" class="mt-2" />
                        </div>
                    </div>
                </div>
            </div>
        </x-common.component-card>

        <x-common.component-card title="Contact details" desc="Shown on the Contact Us page and in the site footer.">
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <div>
                    <label for="contact_address" class="{{ $labelClass }}">Address</label>
                    <input id="contact_address" wire:model="contact_address" type="text" class="{{ $inputClass }}">
                </div>
                <div>
                    <label for="contact_phone" class="{{ $labelClass }}">Phone</label>
                    <input id="contact_phone" wire:model="contact_phone" type="text" class="{{ $inputClass }}">
                </div>
                <div>
                    <label for="contact_email" class="{{ $labelClass }}">Email</label>
                    <input id="contact_email" wire:model="contact_email" type="text" class="{{ $inputClass }}">
                    <x-input-error :messages="$errors->get('contact_email')" class="mt-2" />
                </div>
                <div>
                    <label for="contact_map_url" class="{{ $labelClass }}">Google Maps embed URL</label>
                    <input id="contact_map_url" wire:model="contact_map_url" type="text" placeholder="https://www.google.com/maps/embed?..." class="{{ $inputClass }}">
                    <x-input-error :messages="$errors->get('contact_map_url')" class="mt-2" />
                </div>
                <div class="lg:col-span-2">
                    <label for="contact_hours" class="{{ $labelClass }}">Opening hours</label>
                    <textarea id="contact_hours" wire:model="contact_hours" rows="3" placeholder="Mon-Fri: 8:00 AM - 6:00 PM&#10;Sat: 9:00 AM - 1:00 PM" class="{{ $textareaClass }}"></textarea>
                </div>
            </div>
        </x-common.component-card>

        <x-common.component-card title="Social links & footer" desc="Optional - leave blank to hide.">
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
                <div>
                    <label for="facebook_url" class="{{ $labelClass }}">Facebook URL</label>
                    <input id="facebook_url" wire:model="facebook_url" type="text" class="{{ $inputClass }}">
                    <x-input-error :messages="$errors->get('facebook_url')" class="mt-2" />
                </div>
                <div>
                    <label for="instagram_url" class="{{ $labelClass }}">Instagram URL</label>
                    <input id="instagram_url" wire:model="instagram_url" type="text" class="{{ $inputClass }}">
                    <x-input-error :messages="$errors->get('instagram_url')" class="mt-2" />
                </div>
                <div>
                    <label for="telegram_url" class="{{ $labelClass }}">Telegram URL</label>
                    <input id="telegram_url" wire:model="telegram_url" type="text" class="{{ $inputClass }}">
                    <x-input-error :messages="$errors->get('telegram_url')" class="mt-2" />
                </div>
                <div class="lg:col-span-3">
                    <label for="footer_note" class="{{ $labelClass }}">Footer note</label>
                    <textarea id="footer_note" wire:model="footer_note" rows="2" class="{{ $textareaClass }}"></textarea>
                </div>
            </div>
        </x-common.component-card>

        <div class="flex justify-end">
            <x-ui.button type="submit">{{ __('Save Website Content') }}</x-ui.button>
        </div>
    </form>
</div>
