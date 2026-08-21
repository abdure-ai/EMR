<?php

namespace App\Livewire\Appointments;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class AppointmentEdit extends Component
{
    public Appointment $appointment;

    public string $service_id = '';

    public string $practitioner_id = '';

    public string $scheduled_date = '';

    public string $scheduled_time = '';

    public string $status = '';

    public string $notes = '';

    public function mount(Appointment $appointment): void
    {
        Gate::authorize('update', $appointment);

        $this->appointment = $appointment;
        $this->service_id = (string) $appointment->service_id;
        $this->practitioner_id = (string) $appointment->practitioner_id;
        $this->scheduled_date = $appointment->scheduled_at->toDateString();
        $this->scheduled_time = $appointment->scheduled_at->format('H:i');
        $this->status = $appointment->status;
        $this->notes = (string) $appointment->notes;
    }

    public function save()
    {
        Gate::authorize('update', $this->appointment);

        $validated = $this->validate([
            'service_id' => ['required', 'exists:services,id'],
            'practitioner_id' => ['required', 'exists:users,id'],
            'scheduled_date' => ['required', 'date'],
            'scheduled_time' => ['required', 'date_format:H:i'],
            'status' => ['required', 'in:booked,confirmed,awaiting_payment,checked_in,completed,no_show,cancelled'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->appointment->update([
            'service_id' => $validated['service_id'],
            'practitioner_id' => $validated['practitioner_id'],
            'scheduled_at' => "{$validated['scheduled_date']} {$validated['scheduled_time']}",
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?: null,
        ]);

        session()->flash('status', 'Appointment updated.');

        return $this->redirect(route('appointments.index', ['date' => $validated['scheduled_date']]), navigate: true);
    }

    public function render()
    {
        return view('livewire.appointments.appointment-edit', [
            'services' => Service::where('is_active', true)->orderBy('name')->get(),
            'practitioners' => User::role('Practitioner')->orderBy('name')->get(),
        ])->extends('layouts.app')->title('Edit Appointment');
    }
}
