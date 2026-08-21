<?php

namespace App\Livewire\Inventory;

use App\Models\InventoryBatch;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class StockMovementIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $type = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', InventoryBatch::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingType(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'type');
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.inventory.stock-movement-index', [
            'movements' => StockMovement::with(['medication', 'batch', 'prescription.patient', 'creator'])
                ->when($this->search, fn ($q) => $q->whereHas('medication', fn ($m) => $m->where('name', 'like', "%{$this->search}%")))
                ->when($this->type, fn ($q) => $q->where('type', $this->type))
                ->orderByDesc('created_at')
                ->paginate(20),
        ])->extends('layouts.app')->title('Stock Ledger');
    }
}
