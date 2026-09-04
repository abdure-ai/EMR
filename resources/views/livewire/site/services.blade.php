<div>
    <section class="mx-auto max-w-7xl px-5 py-16 text-center lg:px-8">
        <span class="inline-flex items-center gap-2 rounded-full border border-clinic-sage-200 bg-white px-4 py-1.5 text-xs font-medium uppercase tracking-[0.14em] text-clinic-sage-700">
            {{ __('Our Services') }}
        </span>
        <h1 class="mx-auto mt-6 max-w-2xl font-fraunces text-4xl font-semibold leading-tight text-clinic-ink sm:text-5xl">
            {{ __('Treatments Rooted in Tradition, Guided by Care') }}
        </h1>
        <p class="mx-auto mt-5 max-w-xl text-clinic-ink-soft">
            {{ __('Every treatment plan starts with a consultation, so we understand your needs before recommending a path forward.') }}
        </p>
    </section>

    <section class="mx-auto max-w-7xl px-5 pb-20 lg:px-8">
        @if ($services->isEmpty())
            <div class="rounded-2xl border border-dashed border-clinic-sage-300 bg-white p-14 text-center text-clinic-ink-soft">
                {{ __('Our service listing is being updated - please check back soon, or') }} <a href="{{ route('site.contact') }}" wire:navigate class="text-clinic-sage-700 underline">{{ __('contact us') }}</a> {{ __('directly.') }}
            </div>
        @else
            <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($services as $service)
                    <div x-data="tiltCard()" @mousemove="handleMove($event)" @mouseleave="reset()" :style="tiltStyle"
                         class="flex flex-col overflow-hidden rounded-2xl border border-clinic-sage-200/70 bg-white transition-transform duration-150 ease-out [transform-style:preserve-3d] will-change-transform">
                        <div class="aspect-[16/10] bg-clinic-sage-100">
                            @if ($service->image_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($service->image_path) }}" alt="" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-clinic-sage-400">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none"><path d="M12 2C9 6 5 8.5 5 13a7 7 0 0 0 14 0c0-4.5-4-7-7-11Z" stroke="currentColor" stroke-width="1.3"/></svg>
                                </div>
                            @endif
                        </div>
                        <div class="flex flex-1 flex-col p-6">
                            @if ($service->department)
                                <span class="text-xs font-medium uppercase tracking-wide text-clinic-sage-600">{{ $service->department->name }}</span>
                            @endif
                            <h3 class="mt-1.5 font-fraunces text-xl font-semibold text-clinic-ink">{{ $service->name }}</h3>
                            @if ($service->description)
                                <p class="mt-2.5 flex-1 text-sm leading-relaxed text-clinic-ink-soft">{{ $service->description }}</p>
                            @endif
                            <div class="mt-5 flex items-center justify-between border-t border-clinic-sage-100 pt-4">
                                <span class="text-sm text-clinic-ink-faint">{{ __(':minutes min session', ['minutes' => $service->duration_minutes]) }}</span>
                                <a href="{{ route('site.contact') }}" wire:navigate class="text-sm font-medium text-clinic-sage-700 hover:text-clinic-sage-800">{{ __('Book') }} <span class="inline-block rtl:rotate-180">&rarr;</span></a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</div>
