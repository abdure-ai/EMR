<?php

namespace App\Livewire\Dashboards;

use App\Models\ClinicSetting;
use App\Models\Medication;
use App\Models\Prescription;
use Livewire\Component;

class PharmacyDashboard extends Component
{
    public function render()
    {
        $expiryAlertDays = ClinicSetting::current()->expiry_alert_days;

        $lowStockCount = Medication::whereNotNull('reorder_level')
            ->get()
            ->filter(fn (Medication $medication) => $medication->isLowStock())
            ->count();

        $expiringSoonCount = Medication::whereHas('batches', function ($query) use ($expiryAlertDays) {
            $query->where('quantity_remaining', '>', 0)
                ->whereNotNull('expiry_date')
                ->whereBetween('expiry_date', [now()->toDateString(), now()->addDays($expiryAlertDays)->toDateString()]);
        })->count();

        return view('livewire.dashboards.pharmacy-dashboard', [
            'pendingPrescriptions' => Prescription::where('status', 'pending')->whereHas('items')->count(),
            'dispensedToday' => Prescription::where('status', 'dispensed')->whereDate('dispensed_at', today())->count(),
            'lowStockCount' => $lowStockCount,
            'expiringSoonCount' => $expiringSoonCount,
        ])->extends('layouts.app')->title('Pharmacy');
    }
}
