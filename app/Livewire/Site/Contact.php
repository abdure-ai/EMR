<?php

namespace App\Livewire\Site;

use App\Models\ContactMessage;
use App\Models\SiteContent;
use Livewire\Component;

class Contact extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $subject = '';

    public string $message = '';

    public bool $sent = false;

    public function send(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:60'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        ContactMessage::create($validated);

        $this->reset(['name', 'email', 'phone', 'subject', 'message']);
        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.site.contact', [
            'content' => SiteContent::current(),
        ])->extends('layouts.public')->title('Contact Us');
    }
}
