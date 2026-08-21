<?php

namespace App\Livewire\Billing;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class InvoiceIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Invoice::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage('pendingPage');
        $this->resetPage('paidPage');
    }

    public function clearFilters(): void
    {
        $this->reset('search');
        $this->resetPage('pendingPage');
        $this->resetPage('paidPage');
    }

    protected function searchInvoices($query)
    {
        $term = $this->search;

        return $query->where(function ($q) use ($term) {
            $q->where('invoice_number', 'like', "%{$term}%")
                ->orWhereHas('patient', function ($patientQuery) use ($term) {
                    $patientQuery->where('patient_id', 'like', "%{$term}%")
                        ->orWhere('first_name', 'like', "%{$term}%")
                        ->orWhere('last_name', 'like', "%{$term}%");
                });
        });
    }

    public function render()
    {
        return view('livewire.billing.invoice-index', [
            'pendingInvoices' => Invoice::with(['patient', 'practitioner'])
                ->where('status', 'pending')
                ->when($this->search, fn ($q) => $this->searchInvoices($q))
                ->orderBy('created_at')
                ->paginate(10, ['*'], 'pendingPage'),
            'paidToday' => Invoice::with(['patient'])
                ->where('status', 'paid')
                ->whereDate('paid_at', today())
                ->when($this->search, fn ($q) => $this->searchInvoices($q))
                ->orderByDesc('paid_at')
                ->paginate(10, ['*'], 'paidPage'),
            'pendingCount' => Invoice::where('status', 'pending')->count(),
            'paidTodayCount' => Invoice::where('status', 'paid')->whereDate('paid_at', today())->count(),
            'revenueToday' => Payment::today()->sum('amount'),
        ])->extends('layouts.app')->title('Billing');
    }
}
