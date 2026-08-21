<div
    x-data
    x-show="$store.palette.open"
    x-cloak
    x-init="$watch('$store.palette.open', value => { if (value) { $nextTick(() => $refs.query.focus()) } else { $wire.set('query', '') } })"
    class="fixed inset-0 z-99999 flex items-start justify-center bg-gray-900/50 px-4 pt-24 backdrop-blur-sm"
    @click.self="$store.palette.close()"
>
    <div class="w-full max-w-xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark">
        <div class="relative border-b border-gray-100 dark:border-gray-800">
            <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                <svg width="18" height="18" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04175 9.37363C3.04175 5.87693 5.87711 3.04199 9.37508 3.04199C12.8731 3.04199 15.7084 5.87693 15.7084 9.37363C15.7084 12.8703 12.8731 15.7053 9.37508 15.7053C5.87711 15.7053 3.04175 12.8703 3.04175 9.37363ZM9.37508 1.54199C5.04902 1.54199 1.54175 5.04817 1.54175 9.37363C1.54175 13.6991 5.04902 17.2053 9.37508 17.2053C11.2674 17.2053 13.003 16.5344 14.357 15.4176L17.177 18.238C17.4699 18.5309 17.9448 18.5309 18.2377 18.238C18.5306 17.9451 18.5306 17.4703 18.2377 17.1774L15.418 14.3573C16.5365 13.0033 17.2084 11.2669 17.2084 9.37363C17.2084 5.04817 13.7011 1.54199 9.37508 1.54199Z" fill="currentColor" />
                </svg>
            </span>
            <input type="text" x-ref="query" wire:model.live.debounce.200ms="query"
                   @keydown.escape="$store.palette.close()"
                   placeholder="Search patients, invoices, or jump to a page..."
                   class="h-14 w-full border-0 bg-transparent py-2.5 pl-12 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:outline-hidden focus:ring-0 dark:text-white/90 dark:placeholder:text-white/30">
        </div>

        <div class="max-h-[420px] overflow-y-auto custom-scrollbar p-2">
            @if ($patients->isNotEmpty())
                <p class="px-3 pb-1 pt-2 text-theme-xs font-medium uppercase text-gray-400">Patients</p>
                @foreach ($patients as $patient)
                    <a href="{{ route('patients.show', $patient) }}" wire:navigate @click="$store.palette.close()"
                       class="flex items-center justify-between rounded-lg px-3 py-2.5 text-sm hover:bg-gray-100 dark:hover:bg-white/5">
                        <span class="text-gray-700 dark:text-gray-300">{{ $patient->full_name }}</span>
                        <span class="font-mono text-theme-xs text-gray-400">{{ $patient->patient_id }}</span>
                    </a>
                @endforeach
            @endif

            @if ($invoices->isNotEmpty())
                <p class="px-3 pb-1 pt-2 text-theme-xs font-medium uppercase text-gray-400">Invoices</p>
                @foreach ($invoices as $invoice)
                    <a href="{{ route('billing.show', $invoice) }}" wire:navigate @click="$store.palette.close()"
                       class="flex items-center justify-between rounded-lg px-3 py-2.5 text-sm hover:bg-gray-100 dark:hover:bg-white/5">
                        <span class="font-mono text-gray-700 dark:text-gray-300">{{ $invoice->invoice_number }}</span>
                        <span class="text-theme-xs capitalize text-gray-400">{{ $invoice->status }}</span>
                    </a>
                @endforeach
            @endif

            @if ($modules->isNotEmpty())
                <p class="px-3 pb-1 pt-2 text-theme-xs font-medium uppercase text-gray-400">Go to</p>
                @foreach ($modules as $module)
                    <a href="{{ $module['path'] }}" wire:navigate @click="$store.palette.close()"
                       class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">
                        {{ $module['name'] }}
                    </a>
                @endforeach
            @endif

            @if ($term !== '' && $patients->isEmpty() && $invoices->isEmpty() && $modules->isEmpty())
                <p class="px-3 py-6 text-center text-theme-sm text-gray-400">No matches for &ldquo;{{ $term }}&rdquo;.</p>
            @endif
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-gray-100 px-4 py-2.5 text-theme-xs text-gray-400 dark:border-gray-800">
            <span><kbd class="rounded border border-gray-200 px-1.5 py-0.5 dark:border-gray-700">Esc</kbd> to close</span>
        </div>
    </div>
</div>
