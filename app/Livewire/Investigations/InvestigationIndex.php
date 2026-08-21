<?php

namespace App\Livewire\Investigations;

use App\Models\Investigation;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class InvestigationIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $category = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Investigation::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategory(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'category');
        $this->resetPage();
    }

    public function toggleActive(int $investigationId): void
    {
        $investigation = Investigation::findOrFail($investigationId);

        Gate::authorize('update', $investigation);

        $investigation->update(['is_active' => ! $investigation->is_active]);
    }

    public function render()
    {
        return view('livewire.investigations.investigation-index', [
            'investigations' => Investigation::query()
                ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('subcategory', 'like', "%{$this->search}%"))
                ->when($this->category, fn ($q) => $q->where('category', $this->category))
                ->orderBy('category')
                ->orderBy('subcategory')
                ->orderBy('name')
                ->paginate(15),
        ])->extends('layouts.app')->title('Investigations');
    }
}
