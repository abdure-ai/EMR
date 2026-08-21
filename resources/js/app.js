import './bootstrap';
import ApexCharts from 'apexcharts';

// flatpickr
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';
// FullCalendar
import { Calendar } from '@fullcalendar/core';

// Livewire v3 bundles and starts its own Alpine instance (via @livewireScripts).
// Do NOT import/start a separate 'alpinejs' package here - two instances fighting
// over window.Alpine breaks directives. Register stores/plugins on 'alpine:init'
// instead.

window.ApexCharts = ApexCharts;
window.flatpickr = flatpickr;
window.FullCalendar = Calendar;

// Theme/sidebar stores live here (not in a per-layout inline <script>) because
// Livewire's redirect(navigate: true) does a client-side SPA transition that
// never re-runs a destination page's <head> scripts - only its own JS bundle,
// which is this same app.js on every layout. Alpine only fires 'alpine:init'
// once per session, so if these stores were registered per-layout instead, a
// redirect from a layout that doesn't define them (e.g. the login page) to one
// that does (the dashboard shell) would leave $store.sidebar undefined there.
document.addEventListener('alpine:init', () => {
    Alpine.store('theme', {
        theme: localStorage.getItem('theme') ||
            (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'),
        init() {
            this.apply();
        },
        toggle() {
            this.theme = this.theme === 'light' ? 'dark' : 'light';
            localStorage.setItem('theme', this.theme);
            this.apply();
        },
        apply() {
            document.documentElement.classList.toggle('dark', this.theme === 'dark');
        },
    });

    // Reports revenue chart. The container is wire:ignore'd (see
    // reports/report-index.blade.php) so Livewire's morph never touches it;
    // ReportIndex::dispatchChartUpdate() pushes fresh series/categories here
    // via a browser event whenever the date range filter changes.
    Alpine.data('reportRevenueChart', (initialSeries, initialCategories) => ({
        chart: null,
        init() {
            this.chart = new ApexCharts(this.$refs.canvas, {
                series: [{ name: 'Revenue (ETB)', data: initialSeries }],
                chart: { type: 'area', height: 280, toolbar: { show: false }, fontFamily: 'Outfit, sans-serif' },
                colors: ['#465fff'],
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2 },
                fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0 } },
                xaxis: { categories: initialCategories, axisBorder: { show: false }, axisTicks: { show: false } },
                yaxis: { labels: { formatter: (val) => Number(val).toLocaleString() } },
                grid: { yaxis: { lines: { show: true } } },
                tooltip: { y: { formatter: (val) => Number(val).toLocaleString() + ' ETB' } },
            });
            this.chart.render();

            this.$wire.on('revenue-chart-updated', ({ categories, series }) => {
                this.chart.updateOptions({ xaxis: { categories } });
                this.chart.updateSeries([{ name: 'Revenue (ETB)', data: series }]);
            });
        },
    }));

    Alpine.store('palette', {
        open: false,
        toggle() {
            this.open = !this.open;
        },
        close() {
            this.open = false;
        },
    });

    Alpine.store('sidebar', {
        isExpanded: window.innerWidth >= 1280,
        isMobileOpen: false,
        isHovered: false,
        toggleExpanded() {
            this.isExpanded = !this.isExpanded;
            this.isMobileOpen = false;
        },
        toggleMobileOpen() {
            this.isMobileOpen = !this.isMobileOpen;
        },
        setMobileOpen(val) {
            this.isMobileOpen = val;
        },
        setHovered(val) {
            if (window.innerWidth >= 1280 && !this.isExpanded) {
                this.isHovered = val;
            }
        },
    });
});

// Global command-palette shortcut. Registered once here (not per-layout)
// for the same reason the theme/sidebar stores live here - it must survive
// wire:navigate SPA transitions, which never re-run a destination page's
// inline <head>/<body> scripts.
document.addEventListener('keydown', (event) => {
    if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
        event.preventDefault();
        window.Alpine.store('palette').toggle();
    }
});

// Livewire's wire:navigate transitions sync the <html> tag's attributes to
// match the freshly-fetched page's raw server HTML (see swapCurrentPageWithNewHtml
// -> replaceHtmlAttributes in livewire.js). Since the 'dark' class is only ever
// added client-side (never rendered server-side), every navigation strips it
// back off. Re-apply the saved theme after each navigation to counter that.
document.addEventListener('livewire:navigated', () => {
    window.Alpine.store('theme').apply();
});

// Initialize components on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    // Map imports
    if (document.querySelector('#mapOne')) {
        import('./components/map').then(module => module.initMap());
    }

    // Chart imports
    if (document.querySelector('#chartOne')) {
        import('./components/chart/chart-1').then(module => module.initChartOne());
    }
    if (document.querySelector('#chartTwo')) {
        import('./components/chart/chart-2').then(module => module.initChartTwo());
    }
    if (document.querySelector('#chartThree')) {
        import('./components/chart/chart-3').then(module => module.initChartThree());
    }
    if (document.querySelector('#chartSix')) {
        import('./components/chart/chart-6').then(module => module.initChartSix());
    }
    if (document.querySelector('#chartEight')) {
        import('./components/chart/chart-8').then(module => module.initChartEight());
    }
    if (document.querySelector('#chartThirteen')) {
        import('./components/chart/chart-13').then(module => module.initChartThirteen());
    }

    // Calendar init
    if (document.querySelector('#calendar')) {
        import('./components/calendar-init').then(module => module.calendarInit());
    }
});
