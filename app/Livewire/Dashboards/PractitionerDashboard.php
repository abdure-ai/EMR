<?php

namespace App\Livewire\Dashboards;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\QueueEntry;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class PractitionerDashboard extends Component
{
    public function start(int $queueEntryId): void
    {
        $entry = QueueEntry::findOrFail($queueEntryId);

        Gate::authorize('update', $entry);

        $entry->update(['status' => 'with_practitioner', 'started_at' => now()]);
    }

    public function complete(int $queueEntryId): void
    {
        $entry = QueueEntry::findOrFail($queueEntryId);

        Gate::authorize('update', $entry);

        $entry->update(['status' => 'completed', 'completed_at' => now()]);

        if ($entry->appointment_id) {
            $entry->appointment->update(['status' => 'completed']);
        }
    }

    public function cancel(int $queueEntryId): void
    {
        $entry = QueueEntry::findOrFail($queueEntryId);

        Gate::authorize('update', $entry);

        $entry->update(['status' => 'cancelled']);
    }

    public function render()
    {
        $userId = auth()->id();

        return view('livewire.dashboards.practitioner-dashboard', [
            'totalPatients' => Patient::count(),
            'appointmentsToday' => Appointment::today()->where('practitioner_id', $userId)->count(),
            'myQueue' => QueueEntry::today()
                ->with('patient')
                ->where('practitioner_id', $userId)
                ->whereIn('status', ['waiting', 'with_practitioner'])
                ->orderBy('check_in_time')
                ->get(),
        ])->extends('layouts.app')->title('Practitioner');
    }
}
