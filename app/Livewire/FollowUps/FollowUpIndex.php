<?php

namespace App\Livewire\FollowUps;

use App\Models\Encounter;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class FollowUpIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->can('followups.view'), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function dismiss(int $encounterId): void
    {
        abort_unless(auth()->user()->can('followups.manage'), 403);

        $encounter = Encounter::findOrFail($encounterId);
        $encounter->update(['follow_up_dismissed_at' => now()]);

        session()->flash('status', 'Follow-up dismissed.');
    }

    public function render()
    {
        $items = Encounter::dueForFollowUp();

        if ($this->search) {
            $term = mb_strtolower($this->search);
            $items = $items->filter(fn (Encounter $e) => str_contains(mb_strtolower($e->patient->full_name), $term)
                || str_contains(mb_strtolower($e->patient->patient_id), $term))->values();
        }

        $perPage = 15;
        $page = $this->getPage();

        $followUps = new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('livewire.follow-ups.follow-up-index', [
            'followUps' => $followUps,
            'canManage' => auth()->user()->can('followups.manage'),
        ])->extends('layouts.app')->title('Follow-ups');
    }
}
