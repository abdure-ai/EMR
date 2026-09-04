@php
    $paragraphs = collect(preg_split('/\n\s*\n/', trim((string) $content->about_body)))->filter();
@endphp

<div>
    <section class="mx-auto max-w-7xl px-5 py-16 lg:px-8 lg:py-24">
        <div class="grid grid-cols-1 items-center gap-14 lg:grid-cols-2">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-clinic-sage-200 bg-white px-4 py-1.5 text-xs font-medium uppercase tracking-[0.14em] text-clinic-sage-700">
                    {{ __('About Us') }}
                </span>
                <h1 class="mt-6 font-fraunces text-4xl font-semibold leading-tight text-clinic-ink sm:text-5xl">
                    {{ $content->about_heading ?: __('Our Story') }}
                </h1>

                @if ($paragraphs->isNotEmpty())
                    <div class="prose-clinic mt-6 max-w-xl text-base leading-relaxed text-clinic-ink-soft">
                        @foreach ($paragraphs as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                    </div>
                @else
                    <p class="mt-6 max-w-xl text-base leading-relaxed text-clinic-ink-soft">
                        {{ __('Nesiha Herbal Clinic brings together traditional Islamic herbal remedies and attentive, modern clinical care - content for this page is managed from the admin dashboard.') }}
                    </p>
                @endif
            </div>

            <div class="aspect-[4/5] overflow-hidden rounded-[2rem] border border-clinic-sage-200/70 bg-clinic-sage-100 shadow-xl shadow-clinic-sage-900/10">
                @if ($content->about_image_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($content->about_image_path) }}" alt="" class="h-full w-full object-cover">
                @else
                    <div class="flex h-full w-full items-center justify-center">
                        <svg width="88" height="88" viewBox="0 0 24 24" fill="none" class="text-clinic-sage-400"><path d="M12 2C9 6 5 8.5 5 13a7 7 0 0 0 14 0c0-4.5-4-7-7-11Z" stroke="currentColor" stroke-width="1.3"/></svg>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-5 pb-20 lg:px-8">
        <div class="flex flex-col items-center gap-6 rounded-[2rem] bg-clinic-sage-800 px-8 py-14 text-center sm:px-16">
            <h2 class="font-fraunces text-3xl font-semibold text-white sm:text-4xl">{{ __('Meet us in person') }}</h2>
            <p class="max-w-xl text-clinic-sage-100">{{ __('Visit the clinic or reach out to learn more about our approach to herbal care.') }}</p>
            <a href="{{ route('site.contact') }}" wire:navigate class="rounded-full bg-white px-7 py-3.5 text-sm font-medium text-clinic-sage-800 transition hover:bg-clinic-cream-100">
                {{ __('Contact Us') }}
            </a>
        </div>
    </section>
</div>
