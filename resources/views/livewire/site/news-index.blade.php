<div>
    <section class="mx-auto max-w-7xl px-5 py-16 text-center lg:px-8">
        <span class="inline-flex items-center gap-2 rounded-full border border-clinic-sage-200 bg-white px-4 py-1.5 text-xs font-medium uppercase tracking-[0.14em] text-clinic-sage-700">
            {{ __('News & Updates') }}
        </span>
        <h1 class="mx-auto mt-6 max-w-2xl font-fraunces text-4xl font-semibold leading-tight text-clinic-ink sm:text-5xl">
            {{ __('From the Clinic') }}
        </h1>
        <p class="mx-auto mt-5 max-w-xl text-clinic-ink-soft">
            {{ __('Announcements, health tips, and updates from the Nesiha team.') }}
        </p>
    </section>

    <section class="mx-auto max-w-7xl px-5 pb-20 lg:px-8">
        @if ($posts->isEmpty())
            <div class="rounded-2xl border border-dashed border-clinic-sage-300 bg-white p-14 text-center text-clinic-ink-soft">
                {{ __('No news posted yet - check back soon.') }}
            </div>
        @else
            <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <a href="{{ route('site.news.show', $post) }}" wire:navigate class="group flex flex-col overflow-hidden rounded-2xl border border-clinic-sage-200/70 bg-white">
                        <div class="aspect-[16/10] bg-clinic-sage-100">
                            @if ($post->image_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($post->image_path) }}" alt="" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                            @endif
                        </div>
                        <div class="flex flex-1 flex-col p-6">
                            <p class="text-xs font-medium uppercase tracking-wide text-clinic-ink-faint">{{ $post->published_at?->format('M j, Y') }}</p>
                            <h3 class="mt-2 font-fraunces text-lg font-semibold text-clinic-ink group-hover:text-clinic-sage-700">{{ $post->title }}</h3>
                            @if ($post->excerpt)
                                <p class="mt-2 flex-1 text-sm leading-relaxed text-clinic-ink-soft">{{ $post->excerpt }}</p>
                            @endif
                            <span class="mt-4 text-sm font-medium text-clinic-sage-700">{{ __('Read more') }} <span class="inline-block rtl:rotate-180">&rarr;</span></span>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-10">{{ $posts->links() }}</div>
        @endif
    </section>
</div>
