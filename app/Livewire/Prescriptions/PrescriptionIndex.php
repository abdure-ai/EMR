<?php

namespace App\Livewire\Prescriptions;

use App\Models\Prescription;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PrescriptionIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $status = 'pending';

    public function mount(): void
    {
        Gate::authorize('viewAny', Prescription::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('search');
        $this->status = 'pending';
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.prescriptions.prescription-index', [
            'prescriptions' => Prescription::with(['patient', 'practitioner'])
                ->withCount('items')
                ->whereHas('items')
                ->when($this->status, fn ($q) => $q->where('status', $this->status))
                ->when($this->search, function ($q) {
                    $term = $this->search;

                    $q->whereHas('patient', function ($patientQuery) use ($term) {
                        $patientQuery->where('patient_id', 'like', "%{$term}%")
                            ->orWhere('first_name', 'like', "%{$term}%")
                            ->orWhere('last_name', 'like', "%{$term}%");
                    });
                })
                ->orderBy('created_at')
                ->paginate(15),
        ])->extends('layouts.app')->title('Prescriptions');
    }
}
