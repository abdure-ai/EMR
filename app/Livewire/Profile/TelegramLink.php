<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Str;
use Livewire\Component;

class TelegramLink extends Component
{
    public function connect(): void
    {
        auth()->user()->update(['telegram_link_code' => Str::random(32)]);
    }

    public function disconnect(): void
    {
        auth()->user()->update([
            'telegram_chat_id' => null,
            'telegram_link_code' => null,
        ]);

        session()->flash('telegram-status', 'Telegram disconnected.');
    }

    public function render()
    {
        return view('livewire.profile.telegram-link', [
            'user' => auth()->user()->fresh(),
            'botUsername' => config('services.telegram.bot_username'),
        ]);
    }
}
