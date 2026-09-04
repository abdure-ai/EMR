@php
    $paragraphs = collect(preg_split('/\n\s*\n/', trim($post->body)))->filter();
@endphp

<div>
    <article class="mx-auto max-w-3xl px-5 py-16 lg:px-8">
        <a href="{{ route('site.news.index') }}" wire:navigate class="text-sm font-medium text-clinic-sage-700 hover:text-clinic-sage-800"><span class="inline-block rtl:rotate-180">&larr;</span> {{ __('Back to News') }}</a>

        <p class="mt-6 text-xs font-medium uppercase tracking-wide text-clinic-ink-faint">{{ $post->published_at?->format('F j, Y') }}</p>
        <h1 class="mt-2 font-fraunces text-3xl font-semibold leading-tight text-clinic-ink sm:text-4xl">{{ $post->title }}</h1>

        @if ($post->image_path)
            <div class="mt-8 aspect-[16/9] overflow-hidden rounded-2xl border border-clinic-sage-200/70">
                <img src="{{ \Illuminate\Support\Facades\Storage::url($post->image_path) }}" alt="" class="h-full w-full object-cover">
            </div>
        @endif

        <div class="prose-clinic mt-9 text-base leading-relaxed text-clinic-ink-soft">
            @foreach ($paragraphs as $paragraph)
                <p>{{ $paragraph }}</p>
            @endforeach
        </div>
    </article>

    @if ($recent->isNotEmpty())
        <section class="border-t border-clinic-sage-200/70 bg-clinic-sage-50/60 py-16">
            <div class="mx-auto max-w-7xl px-5 lg:px-8">
                <h2 class="font-fraunces text-2xl font-semibold text-clinic-ink">{{ __('More News') }}</h2>
                <div class="mt-8 grid grid-cols-1 gap-7 sm:grid-cols-3">
                    @foreach ($recent as $item)
                        <a href="{{ route('site.news.show', $item) }}" wire:navigate class="group block overflow-hidden rounded-2xl border border-clinic-sage-200/70 bg-white">
                            <div class="aspect-[16/10] bg-clinic-sage-100">
                                @if ($item->image_path)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($item->image_path) }}" alt="" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                                @endif
                            </div>
                            <div class="p-5">
                                <p class="text-xs font-medium uppercase tracking-wide text-clinic-ink-faint">{{ $item->published_at?->format('M j, Y') }}</p>
                                <h3 class="mt-1.5 font-fraunces text-base font-semibold text-clinic-ink group-hover:text-clinic-sage-700">{{ $item->title }}</h3>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</div>
