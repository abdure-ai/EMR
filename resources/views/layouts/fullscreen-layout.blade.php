<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Sign In' }} | {{ config('app.name') }}</title>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    {{-- Alpine 'theme'/'sidebar' stores are registered once in resources/js/app.js,
         not here - see the comment there for why per-layout registration breaks
         cross-shell Livewire navigate(true) redirects. --}}

    <!-- Apply dark mode immediately to prevent flash -->
    <script>
        (function() {
            // Runs in <head>, before <body> exists - document.documentElement
            // (the <html> tag) is the only element safe to touch here.
            const savedTheme = localStorage.getItem('theme');
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            const theme = savedTheme || systemTheme;
            document.documentElement.classList.toggle('dark', theme === 'dark');
        })();
    </script>
</head>

<body class="dark:bg-gray-900" x-data="{ 'loaded': true}" x-init="$store.sidebar.isExpanded = window.innerWidth >= 1280;
const checkMobile = () => {
    if (window.innerWidth < 1280) {
        $store.sidebar.setMobileOpen(false);
        $store.sidebar.isExpanded = false;
    } else {
        $store.sidebar.isMobileOpen = false;
        $store.sidebar.isExpanded = true;
    }
};
window.addEventListener('resize', checkMobile);">

    {{-- preloader --}}
    <x-common.preloader/>
    {{-- preloader end --}}

    @yield('content')

    @livewireScripts
</body>

@stack('scripts')

</html>
