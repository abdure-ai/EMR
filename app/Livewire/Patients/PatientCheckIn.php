<?php

namespace App\Livewire\Patients;

use App\Models\Department;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\QueueEntry;
use App\Models\Service;
use App\Models\User;
use App\Services\CheckInService;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class PatientCheckIn extends Component
{
    public Patient $patient;

    public string $department_id = '';

    public string $service_id = '';

    public string $practitioner_id = '';

    public bool $alreadyActive = false;

    public function mount(Patient $patient): void
    {
        Gate::authorize('create', QueueEntry::class);

        $this->patient = $patient;

        // Don't let reception double-check-in someone who's already waiting,
        // with a practitioner, or has a visit invoice sitting with the cashier.
        $this->alreadyActive = QueueEntry::where('patient_id', $patient->id)
            ->whereIn('status', ['waiting', 'with_practitioner'])
            ->exists()
            || Invoice::where('patient_id', $patient->id)
                ->where('type', 'visit')
                ->where('status', 'pending')
                ->exists();
    }

    public function updatedDepartmentId(): void
    {
        $this->service_id = '';
    }

    public function getServicesProperty()
    {
        if (! $this->department_id) {
            return collect();
        }

        return Service::where('department_id', $this->department_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function checkIn()
    {
        Gate::authorize('create', QueueEntry::class);

        $validated = $this->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'service_id' => ['required', 'exists:services,id'],
            'practitioner_id' => ['required', 'exists:users,id'],
        ]);

        $result = app(CheckInService::class)->checkIn(
            $this->patient,
            User::findOrFail($validated['practitioner_id']),
            Service::findOrFail($validated['service_id']),
            null,
            auth()->user(),
        );

        session()->flash('status', $result['invoice']
            ? "{$this->patient->full_name} sent to the cashier for payment (invoice {$result['invoice']->invoice_number})."
            : "{$this->patient->full_name} checked in - no fee due.");

        return $this->redirect(route('patients.show', $this->patient), navigate: true);
    }

    public function render()
    {
        return view('livewire.patients.patient-check-in', [
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'practitioners' => User::role('Practitioner')->orderBy('name')->get(),
        ])->extends('layouts.app')->title("Check In - {$this->patient->full_name}");
    }
}
