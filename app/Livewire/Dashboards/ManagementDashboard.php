<?php

namespace App\Livewire\Dashboards;

use App\Models\Appointment;
use App\Models\Encounter;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\QueueEntry;
use App\Models\User;
use Livewire\Component;

class ManagementDashboard extends Component
{
    public function render()
    {
        return view('livewire.dashboards.management-dashboard', [
            'patientsToday' => Patient::whereDate('created_at', today())->count(),
            'totalPatients' => Patient::count(),
            'activeStaff' => User::where('is_active', true)->count(),
            'appointmentsToday' => Appointment::today()->count(),
            'queueWaiting' => QueueEntry::today()->whereIn('status', ['waiting', 'with_practitioner'])->count(),
            'revenueToday' => Payment::today()->sum('amount'),
            'followUpsDue' => Encounter::dueForFollowUp()->count(),
        ])->extends('layouts.app')->title('Management');
    }
}
