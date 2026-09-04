<div>
    {{-- Hero --}}
    <section class="relative overflow-hidden">
        {{-- Ambient 3D scene (Three.js) - soft floating forms behind the hero
             content. Lifecycle wired in app.js via livewire:navigating/navigated
             since this is a Livewire SPA page, not a plain static load. --}}
        <div id="heroScene" class="pointer-events-none absolute inset-0 z-0" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -right-32 -top-32 z-0 h-96 w-96 rounded-full bg-clinic-sage-100/50 blur-3xl"></div>
        <div class="pointer-events-none absolute -left-24 top-40 z-0 h-72 w-72 rounded-full bg-clinic-amber-400/10 blur-3xl"></div>

        <div class="relative z-10 mx-auto grid max-w-7xl grid-cols-1 items-center gap-12 px-5 py-16 sm:py-20 lg:grid-cols-2 lg:px-8 lg:py-28">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-clinic-sage-200 bg-white/70 px-4 py-1.5 text-xs font-medium uppercase tracking-[0.14em] text-clinic-sage-700">
                    {{ __('Islamic Herbal Care') }}
                </span>
                <h1 class="mt-6 font-fraunces text-4xl font-semibold leading-[1.08] text-clinic-ink sm:text-5xl lg:text-[3.4rem]">
                    {{ $content->hero_headline }}
                </h1>
                @if ($content->hero_subheadline)
                    <p class="mt-6 max-w-xl text-lg leading-relaxed text-clinic-ink-soft">
                        {{ $content->hero_subheadline }}
                    </p>
                @endif
                <div class="mt-9 flex flex-wrap items-center gap-4">
                    <a href="{{ $content->hero_cta_url ?: route('site.contact') }}" wire:navigate
                       class="rounded-full bg-clinic-sage-600 px-7 py-3.5 text-sm font-medium text-white shadow-lg shadow-clinic-sage-900/15 transition hover:bg-clinic-sage-700">
                        {{ $content->hero_cta_label ?: __('Book an Appointment') }}
                    </a>
                    <a href="{{ route('site.services') }}" wire:navigate class="text-sm font-medium text-clinic-sage-800 underline decoration-clinic-sage-300 underline-offset-4 hover:decoration-clinic-sage-600">
                        {{ __('Explore our services') }}
                    </a>
                </div>
            </div>

            <div class="relative" x-data="tiltCard()" @mousemove="handleMove($event)" @mouseleave="reset()">
                <div :style="tiltStyle"
                     class="aspect-[4/3] overflow-hidden rounded-[2rem] border border-clinic-sage-200/70 bg-clinic-sage-100 shadow-xl shadow-clinic-sage-900/10 transition-transform duration-150 ease-out [transform-style:preserve-3d] will-change-transform">
                    @if ($content->hero_image_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($content->hero_image_path) }}" alt="" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full w-full items-center justify-center">
                            <svg width="88" height="88" viewBox="0 0 24 24" fill="none" class="text-clinic-sage-400"><path d="M12 2C9 6 5 8.5 5 13a7 7 0 0 0 14 0c0-4.5-4-7-7-11Z" stroke="currentColor" stroke-width="1.3"/></svg>
                        </div>
                    @endif
                </div>
                <div class="absolute -bottom-6 -left-6 hidden rounded-2xl border border-clinic-sage-200 bg-white px-5 py-4 shadow-lg sm:block">
                    <p class="font-fraunces text-2xl font-semibold text-clinic-sage-700">100%</p>
                    <p class="text-xs text-clinic-ink-faint">{{ __('Natural & Halal-conscious remedies') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Highlights --}}
    <section class="mx-auto max-w-7xl px-5 py-16 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="font-fraunces text-3xl font-semibold text-clinic-ink">
                {{ $content->home_highlights_heading ?: __('Why Families Trust Nesiha') }}
            </h2>
            @if ($content->home_highlights_body)
                <p class="mt-4 text-clinic-ink-soft">{{ $content->home_highlights_body }}</p>
            @endif
        </div>

        <div class="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-3">
            @foreach ([
                ['icon' => 'M12 2C9 6 5 8.5 5 13a7 7 0 0 0 14 0c0-4.5-4-7-7-11Z', 'title' => __('Certified Herbalists'), 'body' => __('Every treatment plan is guided by practitioners trained in traditional and clinical herbal medicine.')],
                ['icon' => 'M12 21c-4.5-3-8-6.5-8-11a8 8 0 0 1 16 0c0 4.5-3.5 8-8 11Z', 'title' => __('Islamic-Compliant Care'), 'body' => __('Remedies and consultations are delivered with respect for Islamic values, in a welcoming, modest environment.')],
                ['icon' => 'M4 12h4l2-7 4 14 2-7h4', 'title' => __('Personalized Plans'), 'body' => __('No two patients are alike - your history and symptoms shape a treatment plan built just for you.')],
            ] as $item)
                <div x-data="tiltCard()" @mousemove="handleMove($event)" @mouseleave="reset()" :style="tiltStyle"
                     class="rounded-2xl border border-clinic-sage-200/70 bg-white p-7 transition-transform duration-150 ease-out [transform-style:preserve-3d] will-change-transform">
                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-clinic-sage-50 text-clinic-sage-600">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="{{ $item['icon'] }}" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <h3 class="mt-4 font-fraunces text-lg font-semibold text-clinic-ink">{{ $item['title'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-clinic-ink-soft">{{ $item['body'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Featured services --}}
    @if ($services->isNotEmpty())
        <section class="bg-clinic-sage-50/70 py-16">
            <div class="mx-auto max-w-7xl px-5 lg:px-8">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <h2 class="font-fraunces text-3xl font-semibold text-clinic-ink">{{ __('Featured Treatments') }}</h2>
                    <a href="{{ route('site.services') }}" wire:navigate class="text-sm font-medium text-clinic-sage-700 hover:text-clinic-sage-800">{{ __('View all services') }} <span class="inline-block rtl:rotate-180">&rarr;</span></a>
                </div>

                <div class="mt-10 grid grid-cols-1 gap-7 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($services as $service)
                        <div x-data="tiltCard()" @mousemove="handleMove($event)" @mouseleave="reset()" :style="tiltStyle"
                             class="overflow-hidden rounded-2xl border border-clinic-sage-200/70 bg-white transition-transform duration-150 ease-out [transform-style:preserve-3d] will-change-transform">
                            <div class="aspect-[16/10] bg-clinic-sage-100">
                                @if ($service->image_path)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($service->image_path) }}" alt="" class="h-full w-full object-cover">
                                @endif
                            </div>
                            <div class="p-6">
                                <h3 class="font-fraunces text-lg font-semibold text-clinic-ink">{{ $service->name }}</h3>
                                @if ($service->description)
                                    <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-clinic-ink-soft">{{ $service->description }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Latest news --}}
    @if ($posts->isNotEmpty())
        <section class="mx-auto max-w-7xl px-5 py-16 lg:px-8">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <h2 class="font-fraunces text-3xl font-semibold text-clinic-ink">{{ __('From the Clinic') }}</h2>
                <a href="{{ route('site.news.index') }}" wire:navigate class="text-sm font-medium text-clinic-sage-700 hover:text-clinic-sage-800">{{ __('All news') }} <span class="inline-block rtl:rotate-180">&rarr;</span></a>
            </div>

            <div class="mt-10 grid grid-cols-1 gap-7 sm:grid-cols-3">
                @foreach ($posts as $post)
                    <a href="{{ route('site.news.show', $post) }}" wire:navigate class="group block overflow-hidden rounded-2xl border border-clinic-sage-200/70 bg-white">
                        <div class="aspect-[16/10] bg-clinic-sage-100">
                            @if ($post->image_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($post->image_path) }}" alt="" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                            @endif
                        </div>
                        <div class="p-6">
                            <p class="text-xs font-medium uppercase tracking-wide text-clinic-ink-faint">{{ $post->published_at?->format('M j, Y') }}</p>
                            <h3 class="mt-2 font-fraunces text-lg font-semibold text-clinic-ink group-hover:text-clinic-sage-700">{{ $post->title }}</h3>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- CTA banner --}}
    <section class="mx-auto max-w-7xl px-5 pb-20 lg:px-8">
        <div class="flex flex-col items-center gap-6 rounded-[2rem] bg-clinic-sage-800 px-8 py-14 text-center sm:px-16">
            <h2 class="font-fraunces text-3xl font-semibold text-white sm:text-4xl">{{ __('Ready to start your healing journey?') }}</h2>
            <p class="max-w-xl text-clinic-sage-100">{{ __('Reach out today and our team will help you find the right treatment path.') }}</p>
            <a href="{{ route('site.contact') }}" wire:navigate class="rounded-full bg-white px-7 py-3.5 text-sm font-medium text-clinic-sage-800 transition hover:bg-clinic-cream-100">
                {{ __('Contact Us') }}
            </a>
        </div>
    </section>
</div>
