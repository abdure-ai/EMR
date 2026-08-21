<?php

namespace App\Livewire\Dashboards;

use App\Models\Invoice;
use App\Models\Payment;
use Livewire\Component;

class CashierDashboard extends Component
{
    public function render()
    {
        return view('livewire.dashboards.cashier-dashboard', [
            'pendingInvoices' => Invoice::where('status', 'pending')->count(),
            'revenueToday' => Payment::today()->sum('amount'),
            'paymentsToday' => Payment::today()->count(),
        ])->extends('layouts.app')->title('Billing');
    }
}
