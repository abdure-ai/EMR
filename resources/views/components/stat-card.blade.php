@props(['label', 'value', 'hint' => null])

<div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</span>
    <h4 class="mt-2 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ $value }}</h4>
    @if ($hint)
        <p class="mt-1 text-xs text-gray-400">{{ $hint }}</p>
    @endif
</div>
