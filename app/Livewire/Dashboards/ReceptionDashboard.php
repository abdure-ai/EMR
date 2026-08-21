<?php

namespace App\Livewire\Dashboards;

use App\Models\Appointment;
use App\Models\Encounter;
use App\Models\Patient;
use App\Models\QueueEntry;
use Livewire\Component;

class ReceptionDashboard extends Component
{
    public function render()
    {
        return view('livewire.dashboards.reception-dashboard', [
            'patientsToday' => Patient::whereDate('created_at', today())->count(),
            'totalPatients' => Patient::count(),
            'appointmentsToday' => Appointment::today()->count(),
            'queueWaiting' => QueueEntry::today()->whereIn('status', ['waiting', 'with_practitioner'])->count(),
            'awaitingPayment' => Appointment::today()->where('status', 'awaiting_payment')->count(),
            'followUpsDue' => Encounter::dueForFollowUp()->count(),
        ])->extends('layouts.app')->title('Reception');
    }
}
