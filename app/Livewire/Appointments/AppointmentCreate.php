<?php

namespace App\Livewire\Appointments;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class AppointmentCreate extends Component
{
    public string $patientSearch = '';

    public ?int $patient_id = null;

    public ?string $selectedPatientLabel = null;

    public string $service_id = '';

    public string $practitioner_id = '';

    public string $scheduled_date = '';

    public string $scheduled_time = '09:00';

    public string $source = 'phone';

    public string $notes = '';

    public function mount(): void
    {
        Gate::authorize('create', Appointment::class);

        $this->scheduled_date = today()->toDateString();
    }

    public function selectPatient(int $patientId): void
    {
        $patient = Patient::findOrFail($patientId);

        $this->patient_id = $patient->id;
        $this->selectedPatientLabel = "{$patient->full_name} ({$patient->patient_id})";
        $this->patientSearch = '';
    }

    public function clearPatient(): void
    {
        $this->patient_id = null;
        $this->selectedPatientLabel = null;
    }

    public function getPatientResultsProperty()
    {
        if (mb_strlen($this->patientSearch) < 2) {
            return collect();
        }

        return Patient::query()
            ->where(function ($q) {
                $q->where('patient_id', 'like', "%{$this->patientSearch}%")
                    ->orWhere('first_name', 'like', "%{$this->patientSearch}%")
                    ->orWhere('last_name', 'like', "%{$this->patientSearch}%")
                    ->orWhere('phone', 'like', "%{$this->patientSearch}%");
            })
            ->limit(8)
            ->get();
    }

    public function save()
    {
        Gate::authorize('create', Appointment::class);

        $validated = $this->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'service_id' => ['required', 'exists:services,id'],
            'practitioner_id' => ['required', 'exists:users,id'],
            'scheduled_date' => ['required', 'date'],
            'scheduled_time' => ['required', 'date_format:H:i'],
            'source' => ['required', 'in:website,phone,walk-in,referral'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'patient_id.required' => 'Search for and select a patient.',
        ]);

        $appointment = Appointment::create([
            'patient_id' => $validated['patient_id'],
            'service_id' => $validated['service_id'],
            'practitioner_id' => $validated['practitioner_id'],
            'scheduled_at' => "{$validated['scheduled_date']} {$validated['scheduled_time']}",
            'source' => $validated['source'],
            'notes' => $validated['notes'] ?: null,
            'created_by' => auth()->id(),
        ]);

        session()->flash('status', "Appointment booked for {$appointment->patient->full_name}.");

        return $this->redirect(route('appointments.index', ['date' => $validated['scheduled_date']]), navigate: true);
    }

    public function render()
    {
        return view('livewire.appointments.appointment-create', [
            'services' => Service::where('is_active', true)->orderBy('name')->get(),
            'practitioners' => User::role('Practitioner')->orderBy('name')->get(),
        ])->extends('layouts.app')->title('Book Appointment');
    }
}
