<?php

namespace App\Livewire\Patients;

use App\Models\Patient;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PatientIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $gender = '';

    #[Url(history: true)]
    public string $region = '';

    #[Url(history: true)]
    public string $sort = 'newest';

    #[Url(history: true)]
    public string $scope = 'today';

    public function mount(): void
    {
        Gate::authorize('viewAny', Patient::class);

        // Practitioners work the list FIFO - first registered, first seen.
        // Only steer the default; leave an explicit sort choice (from the URL)
        // alone.
        if ($this->sort === 'newest' && auth()->user()->hasRole('Practitioner')) {
            $this->sort = 'oldest';
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingGender(): void
    {
        $this->resetPage();
    }

    public function updatingRegion(): void
    {
        $this->resetPage();
    }

    public function updatingScope(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'gender', 'region', 'sort', 'scope');
        $this->sort = auth()->user()->hasRole('Practitioner') ? 'oldest' : 'newest';
        $this->resetPage();
    }

    public function delete(int $patientId): void
    {
        $patient = Patient::findOrFail($patientId);

        Gate::authorize('delete', $patient);

        $patient->delete();

        session()->flash('status', "Patient {$patient->patient_id} was archived.");
    }

    public function render()
    {
        $patients = Patient::query()
            ->withExists(['invoices as has_pending_invoice' => fn ($query) => $query->where('status', 'pending')])
            ->withExists('queueEntries')
            ->withExists(['queueEntries as has_active_queue_entry' => fn ($query) => $query->whereIn('status', ['waiting', 'with_practitioner'])])
            ->withExists(['invoices as has_pending_visit_invoice' => fn ($query) => $query->where('type', 'visit')->where('status', 'pending')])
            ->when(auth()->user()->hasRole('Practitioner'), function ($query) {
                // A practitioner only ever sees patients currently checked in
                // and assigned to them. A queue entry is created solely once
                // the visit is paid (or owed nothing) - see
                // CheckInService::checkIn()/processPayment() - so restricting
                // to an active queue entry already means "checked in and paid."
                $query->whereHas('queueEntries', fn ($q) => $q->where('practitioner_id', auth()->id())
                    ->whereIn('status', ['waiting', 'with_practitioner']));
            })
            ->when($this->search, function ($query) {
                $term = $this->search;

                $query->where(function ($q) use ($term) {
                    $q->where('patient_id', 'like', "%{$term}%")
                        ->orWhere('first_name', 'like', "%{$term}%")
                        ->orWhere('middle_name', 'like', "%{$term}%")
                        ->orWhere('last_name', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%");
                });
            })
            ->when($this->gender, fn ($query) => $query->where('sex', $this->gender))
            ->when($this->region, fn ($query) => $query->where('region', $this->region))
            ->when(
                $this->scope === 'today' && ! auth()->user()->hasRole('Practitioner'),
                fn ($query) => $query->whereDate('created_at', today())
            )
            ->when($this->sort === 'newest', fn ($query) => $query->latest())
            ->when($this->sort === 'oldest', fn ($query) => $query->oldest())
            ->when($this->sort === 'name_asc', fn ($query) => $query->orderBy('first_name'))
            ->when($this->sort === 'name_desc', fn ($query) => $query->orderByDesc('first_name'))
            ->paginate(15);

        return view('livewire.patients.patient-index', [
            'patients' => $patients,
            'regionOptions' => Patient::query()
                ->whereNotNull('region')
                ->distinct()
                ->orderBy('region')
                ->pluck('region'),
        ])->extends('layouts.app')->title('Patients');
    }
}
