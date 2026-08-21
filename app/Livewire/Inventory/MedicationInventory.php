<?php

namespace App\Livewire\Inventory;

use App\Models\ClinicSetting;
use App\Models\InventoryBatch;
use App\Models\Medication;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class MedicationInventory extends Component
{
    public Medication $medication;

    public string $batch_number = '';

    public string $quantity = '';

    public string $unit_cost = '';

    public string $expiry_date = '';

    public string $received_at = '';

    public string $notes = '';

    public ?int $adjustingBatchId = null;

    public string $adjustDelta = '';

    public string $adjustReason = '';

    public function mount(Medication $medication): void
    {
        Gate::authorize('viewAny', InventoryBatch::class);

        $this->medication = $medication;
        $this->received_at = today()->toDateString();
    }

    public function receiveStock(): void
    {
        Gate::authorize('create', InventoryBatch::class);

        $validated = $this->validate([
            'batch_number' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'expiry_date' => ['nullable', 'date', 'after:today'],
            'received_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        app(InventoryService::class)->receiveStock($this->medication, [
            ...$validated,
            'unit_cost' => $validated['unit_cost'] !== '' ? $validated['unit_cost'] : null,
            'expiry_date' => $validated['expiry_date'] ?: null,
        ], auth()->user());

        $this->reset(['batch_number', 'quantity', 'unit_cost', 'expiry_date', 'notes']);
        $this->received_at = today()->toDateString();

        session()->flash('status', "Stock received for {$this->medication->name}.");
    }

    public function startAdjusting(int $batchId): void
    {
        Gate::authorize('update', InventoryBatch::findOrFail($batchId));

        $this->adjustingBatchId = $batchId;
        $this->adjustDelta = '';
        $this->adjustReason = '';
    }

    public function cancelAdjusting(): void
    {
        $this->adjustingBatchId = null;
    }

    public function adjustStock(): void
    {
        $batch = InventoryBatch::findOrFail($this->adjustingBatchId);

        Gate::authorize('update', $batch);

        $validated = $this->validate([
            'adjustDelta' => ['required', 'integer', 'not_in:0'],
            'adjustReason' => ['required', 'string', 'max:500'],
        ]);

        app(InventoryService::class)->adjustBatch($batch, (int) $validated['adjustDelta'], $validated['adjustReason'], auth()->user());

        $this->adjustingBatchId = null;

        session()->flash('status', 'Stock adjusted.');
    }

    public function render()
    {
        return view('livewire.inventory.medication-inventory', [
            'canManage' => Gate::allows('create', InventoryBatch::class),
            'batches' => $this->medication->batches()->orderByDesc('received_at')->get(),
            'expiryAlertDays' => ClinicSetting::current()->expiry_alert_days,
        ])->extends('layouts.app')->title("Inventory - {$this->medication->name}");
    }
}
