<?php

namespace App\Livewire\Admin;

use App\Models\ContactMessage;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ContactMessageIndex extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $status = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->can('cms.manage'), 403);
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function markRead(int $messageId): void
    {
        abort_unless(auth()->user()->can('cms.manage'), 403);

        ContactMessage::findOrFail($messageId)->update(['is_read' => true]);
    }

    public function delete(int $messageId): void
    {
        abort_unless(auth()->user()->can('cms.manage'), 403);

        ContactMessage::findOrFail($messageId)->delete();

        session()->flash('status', 'Message deleted.');
    }

    public function render()
    {
        return view('livewire.admin.contact-message-index', [
            'messages' => ContactMessage::query()
                ->when($this->status === 'unread', fn ($q) => $q->where('is_read', false))
                ->when($this->status === 'read', fn ($q) => $q->where('is_read', true))
                ->latest('id')
                ->paginate(15),
            'unreadCount' => ContactMessage::where('is_read', false)->count(),
        ])->extends('layouts.app')->title('Contact Messages');
    }
}
