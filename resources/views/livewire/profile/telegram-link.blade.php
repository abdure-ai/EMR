<div>
    @if (session('telegram-status'))
        <div class="mb-4"><x-ui.alert variant="success" title="Updated" :message="session('telegram-status')" /></div>
    @endif

    @if ($user->hasTelegramLinked())
        <div class="flex items-center justify-between">
            <div>
                <x-ui.badge variant="solid" color="success">{{ __('Linked') }}</x-ui.badge>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __("You'll receive new-patient notifications in Telegram.") }}</p>
            </div>
            <x-ui.button wire:click="disconnect" wire:confirm="{{ __('Disconnect your Telegram account?') }}" variant="outline">
                {{ __('Disconnect') }}
            </x-ui.button>
        </div>
    @elseif ($user->telegram_link_code)
        <div class="space-y-3">
            <x-ui.badge variant="solid" color="warning">{{ __('Pending') }}</x-ui.badge>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                {{ __('Open Telegram, tap the button below, then press Start to finish linking.') }}
            </p>
            <a href="https://t.me/{{ $botUsername }}?start={{ $user->telegram_link_code }}" target="_blank"
               class="inline-flex items-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                {{ __('Open in Telegram') }}
            </a>
            <div>
                <x-ui.button wire:click="connect" variant="outline">{{ __('Generate new link') }}</x-ui.button>
            </div>
        </div>
    @else
        <div>
            <x-ui.badge variant="light" color="light">{{ __('Not linked') }}</x-ui.badge>
            <p class="mt-2 mb-4 text-sm text-gray-500 dark:text-gray-400">
                {{ __('Link your Telegram to get notified when a new patient is registered.') }}
            </p>
            <x-ui.button wire:click="connect">{{ __('Connect Telegram') }}</x-ui.button>
        </div>
    @endif
</div>
