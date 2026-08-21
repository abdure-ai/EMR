<?php

namespace App\Livewire\Billing;

use App\Models\Invoice;
use App\Services\CheckInService;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class InvoiceShow extends Component
{
    public Invoice $invoice;

    public string $paymentMethod = 'cash';

    public function mount(Invoice $invoice): void
    {
        Gate::authorize('view', $invoice);

        $this->invoice = $invoice->load(['patient', 'practitioner', 'service', 'lineItems', 'payments', 'creator']);
    }

    public function confirmPayment()
    {
        Gate::authorize('update', $this->invoice);

        $this->validate([
            'paymentMethod' => ['required', 'in:cash,bank,telebirr,cbe_birr,other'],
        ]);

        $queueEntry = app(CheckInService::class)->processPayment($this->invoice, $this->paymentMethod, auth()->user());

        session()->flash('status', $queueEntry
            ? "Payment recorded for {$this->invoice->patient->full_name}. They've been added to {$this->invoice->practitioner->name}'s queue."
            : "Registration payment recorded for {$this->invoice->patient->full_name}.");

        return $this->redirect(route('billing.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.billing.invoice-show')
            ->extends('layouts.app')->title("Invoice {$this->invoice->invoice_number}");
    }
}
