<?php

namespace App\Livewire\Patients;

use App\Models\Invoice;
use App\Models\Patient;
use App\Models\QueueEntry;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class PatientShow extends Component
{
    public Patient $patient;

    public function mount(Patient $patient): void
    {
        Gate::authorize('view', $patient);

        $this->patient = $patient;
    }

    public function render()
    {
        $alreadyActive = QueueEntry::where('patient_id', $this->patient->id)
            ->whereIn('status', ['waiting', 'with_practitioner'])
            ->exists()
            || Invoice::where('patient_id', $this->patient->id)
                ->where('type', 'visit')
                ->where('status', 'pending')
                ->exists();

        return view('livewire.patients.patient-show', [
            'canViewMedical' => Gate::allows('viewMedical', $this->patient),
            'canCheckIn' => Gate::allows('create', QueueEntry::class) && ! $alreadyActive,
            'encounters' => $this->patient->encounters()
                ->with(['practitioner', 'queueEntry'])
                ->latest('created_at')
                ->get(),
        ])->extends('layouts.app')->title($this->patient->full_name);
    }
}
