<?php

namespace App\Livewire\Appointments;

use App\Models\Appointment;
use App\Models\QueueEntry;
use App\Models\User;
use App\Services\CheckInService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class AppointmentIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $date = '';

    #[Url(history: true)]
    public string $practitioner = '';

    #[Url(history: true)]
    public string $status = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Appointment::class);

        if ($this->date === '') {
            $this->date = today()->toDateString();
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'practitioner', 'status');
        $this->date = today()->toDateString();
        $this->resetPage();
    }

    public function confirm(int $appointmentId): void
    {
        $appointment = Appointment::findOrFail($appointmentId);

        Gate::authorize('update', $appointment);

        $appointment->update(['status' => 'confirmed']);
    }

    public function cancel(int $appointmentId): void
    {
        $appointment = Appointment::findOrFail($appointmentId);

        Gate::authorize('update', $appointment);

        $appointment->update(['status' => 'cancelled']);
    }

    /**
     * Reception check-in: if nothing is owed (new-patient fee / revisit fee
     * don't apply), the patient joins the practitioner's queue immediately.
     * Otherwise this sends them to the cashier instead - they only actually
     * join the queue once payment is processed there.
     */
    public function checkIn(int $appointmentId): void
    {
        $appointment = Appointment::findOrFail($appointmentId);

        Gate::authorize('update', $appointment);
        Gate::authorize('create', QueueEntry::class);

        $result = app(CheckInService::class)->checkIn(
            $appointment->patient,
            $appointment->practitioner,
            $appointment->service,
            $appointment,
            auth()->user(),
        );

        session()->flash('status', $result['invoice']
            ? "{$appointment->patient->full_name} sent to the cashier for payment."
            : "{$appointment->patient->full_name} checked in.");
    }

    public function render()
    {
        $user = auth()->user();

        $appointments = Appointment::query()
            ->with(['patient', 'service', 'practitioner'])
            ->when($this->search, function ($q) {
                $term = $this->search;

                $q->whereHas('patient', function ($patientQuery) use ($term) {
                    $patientQuery->where('patient_id', 'like', "%{$term}%")
                        ->orWhere('first_name', 'like', "%{$term}%")
                        ->orWhere('last_name', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%");
                });
            })
            ->when($this->date, fn ($q) => $q->whereDate('scheduled_at', $this->date))
            ->when($this->practitioner, fn ($q) => $q->where('practitioner_id', $this->practitioner))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($user->hasRole('Practitioner'), fn ($q) => $q->where('practitioner_id', $user->id))
            ->orderBy('scheduled_at')
            ->paginate(15);

        return view('livewire.appointments.appointment-index', [
            'appointments' => $appointments,
            'practitioners' => User::role('Practitioner')->orderBy('name')->get(),
        ])->extends('layouts.app')->title('Appointments');
    }
}
