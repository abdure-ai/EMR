<?php

namespace App\Livewire\Medications;

use App\Models\Medication;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class MedicationIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Medication::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('search');
        $this->resetPage();
    }

    public function toggleActive(int $medicationId): void
    {
        $medication = Medication::findOrFail($medicationId);

        Gate::authorize('update', $medication);

        $medication->update(['is_active' => ! $medication->is_active]);
    }

    public function render()
    {
        return view('livewire.medications.medication-index', [
            'medications' => Medication::query()
                ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
                ->orderBy('name')
                ->paginate(15),
        ])->extends('layouts.app')->title('Medications');
    }
}
