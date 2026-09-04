@php
    $siteContent = \App\Models\SiteContent::current();
    $currentLocale = app()->getLocale();
    $isRtl = in_array($currentLocale, \App\Http\Middleware\SetLocale::RTL, true);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $currentLocale) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? __('Home') }} | {{ __('Nesiha Herbal Clinic') }}</title>
    <meta name="description" content="{{ $siteContent->hero_subheadline ?: __('Nesiha Herbal Clinic - trusted Islamic herbal care.') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        [x-cloak] { display: none !important; }
        .prose-clinic p { margin-bottom: 1.1em; }
        .prose-clinic p:last-child { margin-bottom: 0; }
        /* Amharic (Ge'ez) and Arabic script fallbacks - Outfit/Fraunces only
           cover Latin, so Amharic/Arabic text would otherwise fall through
           to the browser's generic system font, clashing with the rest of
           the type system. Afaan Oromo uses Latin script, already covered. */
        html[lang="am"] body, html[lang="am"] .font-fraunces { font-family: 'Noto Sans Ethiopic', Outfit, sans-serif; }
        html[lang="ar"] body, html[lang="ar"] .font-fraunces { font-family: 'Noto Sans Arabic', Outfit, sans-serif; }
    </style>
</head>

<body class="bg-clinic-cream-50 font-outfit text-clinic-ink antialiased" x-data="{ mobileNavOpen: false, langOpen: false }">

    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:start-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-clinic-sage-700 focus:px-4 focus:py-2 focus:text-white">
        {{ __('Skip to content') }}
    </a>

    <header class="sticky top-0 z-50 border-b border-clinic-sage-200/70 bg-clinic-cream-50/90 backdrop-blur-sm">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4 lg:px-8">
            <a href="{{ route('site.home') }}" wire:navigate class="flex items-center gap-2.5">
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-clinic-sage-600 font-fraunces text-lg font-semibold text-clinic-cream-50">N</span>
                <span class="font-fraunces text-lg font-semibold leading-tight text-clinic-sage-800">
                    {{ __('Nesiha') }}<br class="hidden sm:block">
                    <span class="text-xs font-normal tracking-[0.18em] text-clinic-ink-faint">{{ __('HERBAL CLINIC') }}</span>
                </span>
            </a>

            <nav class="hidden items-center gap-9 lg:flex">
                @php
                    $navItems = [
                        ['label' => __('Home'), 'route' => 'site.home'],
                        ['label' => __('About Us'), 'route' => 'site.about'],
                        ['label' => __('Services'), 'route' => 'site.services'],
                        ['label' => __('News'), 'route' => 'site.news.index'],
                        ['label' => __('Contact Us'), 'route' => 'site.contact'],
                    ];
                @endphp
                @foreach ($navItems as $item)
                    @php $active = request()->routeIs($item['route']) || request()->routeIs($item['route'].'.*'); @endphp
                    <a href="{{ route($item['route']) }}" wire:navigate
                       class="text-sm font-medium transition-colors {{ $active ? 'text-clinic-sage-700' : 'text-clinic-ink-soft hover:text-clinic-sage-700' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="hidden items-center gap-4 lg:flex">
                {{-- Language switcher --}}
                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                    <button @click="open = !open" type="button"
                            class="flex items-center gap-1.5 rounded-full border border-clinic-sage-200 px-3.5 py-2 text-sm font-medium text-clinic-ink-soft hover:border-clinic-sage-400 hover:text-clinic-sage-700">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.4"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18 14 14 0 0 1 0-18Z" stroke="currentColor" stroke-width="1.4"/></svg>
                        {{ \App\Http\Middleware\SetLocale::SUPPORTED[$currentLocale] }}
                    </button>
                    <div x-show="open" x-cloak x-transition
                         class="absolute end-0 mt-2 w-40 overflow-hidden rounded-xl border border-clinic-sage-200 bg-white py-1.5 shadow-lg">
                        @foreach (\App\Http\Middleware\SetLocale::SUPPORTED as $code => $name)
                            <a href="{{ route('language.switch', $code) }}"
                               class="flex items-center justify-between px-4 py-2 text-sm {{ $currentLocale === $code ? 'font-semibold text-clinic-sage-700' : 'text-clinic-ink-soft hover:bg-clinic-sage-50' }}">
                                {{ $name }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <a href="{{ route('login') }}" wire:navigate class="text-sm font-medium text-clinic-ink-soft hover:text-clinic-sage-700">{{ __('Staff Login') }}</a>
                <a href="{{ route('site.contact') }}" wire:navigate
                   class="rounded-full bg-clinic-sage-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm shadow-clinic-sage-900/10 transition hover:bg-clinic-sage-700">
                    {{ __('Book an Appointment') }}
                </a>
            </div>

            <button @click="mobileNavOpen = !mobileNavOpen" type="button" aria-label="{{ __('Toggle menu') }}"
                    class="flex h-10 w-10 items-center justify-center rounded-full text-clinic-sage-800 lg:hidden">
                <svg x-show="!mobileNavOpen" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                <svg x-show="mobileNavOpen" x-cloak width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            </button>
        </div>

        <div x-show="mobileNavOpen" x-cloak x-transition class="border-t border-clinic-sage-200/70 bg-clinic-cream-50 px-5 py-4 lg:hidden">
            <nav class="flex flex-col gap-1">
                @foreach ($navItems as $item)
                    <a href="{{ route($item['route']) }}" wire:navigate @click="mobileNavOpen = false"
                       class="rounded-lg px-3 py-2.5 text-sm font-medium text-clinic-ink-soft hover:bg-clinic-sage-50 hover:text-clinic-sage-700">
                        {{ $item['label'] }}
                    </a>
                @endforeach
                <a href="{{ route('login') }}" wire:navigate class="rounded-lg px-3 py-2.5 text-sm font-medium text-clinic-ink-soft hover:bg-clinic-sage-50">{{ __('Staff Login') }}</a>
                <a href="{{ route('site.contact') }}" wire:navigate class="mt-2 rounded-full bg-clinic-sage-600 px-4 py-2.5 text-center text-sm font-medium text-white">{{ __('Book an Appointment') }}</a>

                <div class="mt-3 flex flex-wrap gap-2 border-t border-clinic-sage-200/70 pt-3">
                    @foreach (\App\Http\Middleware\SetLocale::SUPPORTED as $code => $name)
                        <a href="{{ route('language.switch', $code) }}"
                           class="rounded-full border px-3.5 py-1.5 text-xs font-medium {{ $currentLocale === $code ? 'border-clinic-sage-600 bg-clinic-sage-600 text-white' : 'border-clinic-sage-200 text-clinic-ink-soft' }}">
                            {{ $name }}
                        </a>
                    @endforeach
                </div>
            </nav>
        </div>
    </header>

    <main id="main">
        @if (session('status'))
            <div class="mx-auto max-w-3xl px-5 pt-6 lg:px-8">
                <div class="rounded-xl border border-clinic-sage-300 bg-clinic-sage-50 px-4 py-3 text-sm text-clinic-sage-800">
                    {{ session('status') }}
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="mt-24 bg-clinic-sage-900 text-clinic-cream-100">
        <div class="mx-auto grid max-w-7xl grid-cols-1 gap-10 px-5 py-14 sm:grid-cols-2 lg:grid-cols-4 lg:px-8">
            <div>
                <div class="flex items-center gap-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-clinic-cream-50 font-fraunces text-base font-semibold text-clinic-sage-800">N</span>
                    <span class="font-fraunces text-base font-semibold text-clinic-cream-50">{{ __('Nesiha Herbal Clinic') }}</span>
                </div>
                <p class="mt-4 text-sm leading-relaxed text-clinic-sage-200">
                    {{ $siteContent->footer_note ?: __('Trusted Islamic herbal care, rooted in tradition and delivered with modern clinical care.') }}
                </p>
                @if ($siteContent->facebook_url || $siteContent->instagram_url || $siteContent->telegram_url)
                    <div class="mt-5 flex items-center gap-3">
                        @if ($siteContent->facebook_url)
                            <a href="{{ $siteContent->facebook_url }}" target="_blank" rel="noopener" aria-label="Facebook" class="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-clinic-cream-50 transition hover:bg-white/20">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M13.5 21v-7.5h2.5l.5-3H13.5V8.5c0-.9.3-1.5 1.7-1.5H16.5V4.3C16.2 4.3 15.2 4 14 4c-2.5 0-4.2 1.5-4.2 4.3V10.5H7.5v3H9.8V21h3.7Z"/></svg>
                            </a>
                        @endif
                        @if ($siteContent->instagram_url)
                            <a href="{{ $siteContent->instagram_url }}" target="_blank" rel="noopener" aria-label="Instagram" class="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-clinic-cream-50 transition hover:bg-white/20">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="3.6"/><circle cx="16.9" cy="7.1" r="0.9" fill="currentColor" stroke="none"/></svg>
                            </a>
                        @endif
                        @if ($siteContent->telegram_url)
                            <a href="{{ $siteContent->telegram_url }}" target="_blank" rel="noopener" aria-label="Telegram" class="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-clinic-cream-50 transition hover:bg-white/20">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M21 4 2.5 11.3c-1 .4-1 1.7.1 2l4.6 1.4 1.8 5.6c.3.9 1.4 1.1 2 .4l2.6-2.7 4.8 3.5c.9.6 2 .1 2.2-.9L21.9 5c.2-1-.8-1.7-1.7-1.3ZM8.3 14.2l9-6.5-7 7.4-.3 3-1.7-3.9Z"/></svg>
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            <div>
                <p class="font-fraunces text-sm font-semibold text-clinic-cream-50">{{ __('Explore') }}</p>
                <ul class="mt-4 space-y-2.5 text-sm text-clinic-sage-200">
                    <li><a href="{{ route('site.about') }}" wire:navigate class="hover:text-white">{{ __('About Us') }}</a></li>
                    <li><a href="{{ route('site.services') }}" wire:navigate class="hover:text-white">{{ __('Services') }}</a></li>
                    <li><a href="{{ route('site.news.index') }}" wire:navigate class="hover:text-white">{{ __('News') }}</a></li>
                    <li><a href="{{ route('site.contact') }}" wire:navigate class="hover:text-white">{{ __('Contact Us') }}</a></li>
                </ul>
            </div>

            <div>
                <p class="font-fraunces text-sm font-semibold text-clinic-cream-50">{{ __('Visit Us') }}</p>
                <ul class="mt-4 space-y-2.5 text-sm text-clinic-sage-200">
                    @if ($siteContent->contact_address)
                        <li>{{ $siteContent->contact_address }}</li>
                    @endif
                    @if ($siteContent->contact_phone)
                        <li><a href="tel:{{ $siteContent->contact_phone }}" class="hover:text-white">{{ $siteContent->contact_phone }}</a></li>
                    @endif
                    @if ($siteContent->contact_email)
                        <li><a href="mailto:{{ $siteContent->contact_email }}" class="hover:text-white">{{ $siteContent->contact_email }}</a></li>
                    @endif
                </ul>
            </div>

            <div>
                <p class="font-fraunces text-sm font-semibold text-clinic-cream-50">{{ __('Opening Hours') }}</p>
                <p class="mt-4 whitespace-pre-line text-sm leading-relaxed text-clinic-sage-200">{{ $siteContent->contact_hours ?: __('Sat - Thu: 8:00 AM - 6:00 PM') }}</p>
            </div>
        </div>

        <div class="border-t border-white/10 px-5 py-5 text-center text-xs text-clinic-sage-300 lg:px-8">
            &copy; {{ now()->year }} {{ __('Nesiha Herbal Clinic. All rights reserved.') }}
        </div>
    </footer>

    @livewireScripts
</body>
</html>
