<?php

namespace App\Livewire\Inventory;

use App\Models\ClinicSetting;
use App\Models\Medication;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class InventoryIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $status = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', \App\Models\InventoryBatch::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'status');
        $this->resetPage();
    }

    public function render()
    {
        $expiryAlertDays = ClinicSetting::current()->expiry_alert_days;

        $medications = Medication::query()
            ->with(['batches' => fn ($q) => $q->where('quantity_remaining', '>', 0)->orderBy('expiry_date')])
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->status === 'low', function ($q) {
                $q->whereNotNull('reorder_level')
                    ->whereRaw('(select coalesce(sum(ib.quantity_remaining), 0) from inventory_batches ib where ib.medication_id = medications.id) <= medications.reorder_level');
            })
            ->when($this->status === 'expiring', function ($q) use ($expiryAlertDays) {
                $q->whereHas('batches', function ($batchQuery) use ($expiryAlertDays) {
                    $batchQuery->where('quantity_remaining', '>', 0)
                        ->whereNotNull('expiry_date')
                        ->whereBetween('expiry_date', [now()->toDateString(), now()->addDays($expiryAlertDays)->toDateString()]);
                });
            })
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.inventory.inventory-index', [
            'medications' => $medications,
            'expiryAlertDays' => $expiryAlertDays,
        ])->extends('layouts.app')->title('Inventory');
    }
}
