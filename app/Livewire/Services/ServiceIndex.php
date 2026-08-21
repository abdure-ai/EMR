<?php

namespace App\Livewire\Services;

use App\Models\Department;
use App\Models\Service;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ServiceIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $department = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Service::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingDepartment(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'department');
        $this->resetPage();
    }

    public function toggleActive(int $serviceId): void
    {
        $service = Service::findOrFail($serviceId);

        Gate::authorize('update', $service);

        $service->update(['is_active' => ! $service->is_active]);
    }

    public function render()
    {
        return view('livewire.services.service-index', [
            'services' => Service::with('department')
                ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
                ->when($this->department, fn ($q) => $q->where('department_id', $this->department))
                ->orderBy('name')
                ->paginate(15),
            'departments' => Department::orderBy('name')->get(),
        ])->extends('layouts.app')->title('Services');
    }
}
