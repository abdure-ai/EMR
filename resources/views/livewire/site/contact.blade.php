@php
    $inputClass = 'w-full rounded-xl border border-clinic-sage-200 bg-clinic-cream-50 px-4 py-3 text-sm text-clinic-ink placeholder:text-clinic-ink-faint focus:border-clinic-sage-500 focus:outline-none focus:ring-2 focus:ring-clinic-sage-500/20';
    $labelClass = 'mb-1.5 block text-sm font-medium text-clinic-ink';
@endphp

<div>
    <section class="mx-auto max-w-7xl px-5 py-16 text-center lg:px-8">
        <span class="inline-flex items-center gap-2 rounded-full border border-clinic-sage-200 bg-white px-4 py-1.5 text-xs font-medium uppercase tracking-[0.14em] text-clinic-sage-700">
            {{ __('Contact Us') }}
        </span>
        <h1 class="mx-auto mt-6 max-w-2xl font-fraunces text-4xl font-semibold leading-tight text-clinic-ink sm:text-5xl">
            {{ __("We'd Love to Hear From You") }}
        </h1>
        <p class="mx-auto mt-5 max-w-xl text-clinic-ink-soft">
            {{ __('Questions about a treatment, or ready to book your first visit? Reach out and our team will respond shortly.') }}
        </p>
    </section>

    <section class="mx-auto max-w-7xl px-5 pb-20 lg:px-8">
        <div class="grid grid-cols-1 gap-10 lg:grid-cols-5">
            <div class="lg:col-span-2">
                <div class="space-y-5">
                    @if ($content->contact_address)
                        <div class="flex items-start gap-4 rounded-2xl border border-clinic-sage-200/70 bg-white p-5">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-clinic-sage-50 text-clinic-sage-600">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M12 21s7-6.5 7-12a7 7 0 1 0-14 0c0 5.5 7 12 7 12Z" stroke="currentColor" stroke-width="1.5"/><circle cx="12" cy="9" r="2.4" stroke="currentColor" stroke-width="1.5"/></svg>
                            </span>
                            <div>
                                <p class="text-sm font-medium text-clinic-ink">{{ __('Address') }}</p>
                                <p class="mt-0.5 text-sm text-clinic-ink-soft">{{ $content->contact_address }}</p>
                            </div>
                        </div>
                    @endif
                    @if ($content->contact_phone)
                        <div class="flex items-start gap-4 rounded-2xl border border-clinic-sage-200/70 bg-white p-5">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-clinic-sage-50 text-clinic-sage-600">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M5 4h3l2 5-2.5 1.5a11 11 0 0 0 5 5L14 13l5 2v3a2 2 0 0 1-2 2C10.5 20 4 13.5 4 6a2 2 0 0 1 1-2Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/></svg>
                            </span>
                            <div>
                                <p class="text-sm font-medium text-clinic-ink">{{ __('Phone') }}</p>
                                <a href="tel:{{ $content->contact_phone }}" class="mt-0.5 block text-sm text-clinic-ink-soft hover:text-clinic-sage-700">{{ $content->contact_phone }}</a>
                            </div>
                        </div>
                    @endif
                    @if ($content->contact_email)
                        <div class="flex items-start gap-4 rounded-2xl border border-clinic-sage-200/70 bg-white p-5">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-clinic-sage-50 text-clinic-sage-600">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M4 6h16v12H4z" stroke="currentColor" stroke-width="1.4"/><path d="m4 7 8 6 8-6" stroke="currentColor" stroke-width="1.4"/></svg>
                            </span>
                            <div>
                                <p class="text-sm font-medium text-clinic-ink">{{ __('Email') }}</p>
                                <a href="mailto:{{ $content->contact_email }}" class="mt-0.5 block text-sm text-clinic-ink-soft hover:text-clinic-sage-700">{{ $content->contact_email }}</a>
                            </div>
                        </div>
                    @endif
                    @if ($content->contact_hours)
                        <div class="flex items-start gap-4 rounded-2xl border border-clinic-sage-200/70 bg-white p-5">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-clinic-sage-50 text-clinic-sage-600">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.4"/><path d="M12 7.5V12l3 2" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
                            </span>
                            <div>
                                <p class="text-sm font-medium text-clinic-ink">{{ __('Opening Hours') }}</p>
                                <p class="mt-0.5 whitespace-pre-line text-sm text-clinic-ink-soft">{{ $content->contact_hours }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                @if ($content->contact_map_url)
                    <div class="mt-5 overflow-hidden rounded-2xl border border-clinic-sage-200/70">
                        <iframe src="{{ $content->contact_map_url }}" class="h-64 w-full" style="border:0" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                @endif
            </div>

            <div class="lg:col-span-3">
                <div class="rounded-[1.75rem] border border-clinic-sage-200/70 bg-white p-7 sm:p-9">
                    @if ($sent)
                        <div class="flex flex-col items-center gap-4 py-10 text-center">
                            <span class="flex h-14 w-14 items-center justify-center rounded-full bg-clinic-sage-50 text-clinic-sage-600">
                                <svg width="26" height="26" viewBox="0 0 24 24" fill="none"><path d="m5 13 4 4 10-10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <h3 class="font-fraunces text-xl font-semibold text-clinic-ink">{{ __('Message sent') }}</h3>
                            <p class="max-w-sm text-sm text-clinic-ink-soft">{{ __('Thank you for reaching out - our team will get back to you soon.') }}</p>
                            <button wire:click="$set('sent', false)" type="button" class="mt-2 text-sm font-medium text-clinic-sage-700 hover:text-clinic-sage-800">{{ __('Send another message') }}</button>
                        </div>
                    @else
                        <form wire:submit="send" class="space-y-5">
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <div>
                                    <label for="name" class="{{ $labelClass }}">{{ __('Full name') }}</label>
                                    <input id="name" wire:model="name" type="text" class="{{ $inputClass }}">
                                    <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
                                </div>
                                <div>
                                    <label for="email" class="{{ $labelClass }}">{{ __('Email') }}</label>
                                    <input id="email" wire:model="email" type="text" class="{{ $inputClass }}">
                                    <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <div>
                                    <label for="phone" class="{{ $labelClass }}">{{ __('Phone (optional)') }}</label>
                                    <input id="phone" wire:model="phone" type="text" class="{{ $inputClass }}">
                                </div>
                                <div>
                                    <label for="subject" class="{{ $labelClass }}">{{ __('Subject (optional)') }}</label>
                                    <input id="subject" wire:model="subject" type="text" class="{{ $inputClass }}">
                                </div>
                            </div>
                            <div>
                                <label for="message" class="{{ $labelClass }}">{{ __('Message') }}</label>
                                <textarea id="message" wire:model="message" rows="5" class="{{ $inputClass }}"></textarea>
                                <x-input-error :messages="$errors->get('message')" class="mt-1.5" />
                            </div>
                            <button type="submit" wire:loading.attr="disabled" wire:target="send"
                                    class="w-full rounded-full bg-clinic-sage-600 px-6 py-3.5 text-sm font-medium text-white transition hover:bg-clinic-sage-700 disabled:opacity-60 sm:w-auto">
                                <span wire:loading.remove wire:target="send">{{ __('Send Message') }}</span>
                                <span wire:loading wire:target="send">{{ __('Sending…') }}</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
