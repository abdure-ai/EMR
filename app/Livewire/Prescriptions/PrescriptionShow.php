<?php

namespace App\Livewire\Prescriptions;

use App\Models\Prescription;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class PrescriptionShow extends Component
{
    public Prescription $prescription;

    public function mount(Prescription $prescription): void
    {
        Gate::authorize('view', $prescription);

        $this->prescription = $prescription->load(['patient', 'practitioner', 'items.medication', 'dispenser']);
    }

    public function dispense(): void
    {
        Gate::authorize('dispense', $this->prescription);

        $shortfalls = app(InventoryService::class)->deductForPrescription($this->prescription, auth()->user());

        $this->prescription->update([
            'status' => 'dispensed',
            'dispensed_at' => now(),
            'dispensed_by' => auth()->id(),
        ]);

        $message = "Prescription for {$this->prescription->patient->full_name} marked as dispensed.";

        if ($shortfalls) {
            $message .= ' Insufficient stock recorded for: '.implode('; ', $shortfalls).'.';
        }

        session()->flash('status', $message);

        $this->redirect(route('prescriptions.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.prescriptions.prescription-show', [
            'canDispense' => Gate::allows('dispense', $this->prescription),
        ])->extends('layouts.app')->title("Prescription - {$this->prescription->patient->full_name}");
    }
}
