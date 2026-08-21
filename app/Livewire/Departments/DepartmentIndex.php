<?php

namespace App\Livewire\Departments;

use App\Models\Department;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class DepartmentIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Department::class);
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

    public function toggleActive(int $departmentId): void
    {
        $department = Department::findOrFail($departmentId);

        Gate::authorize('update', $department);

        $department->update(['is_active' => ! $department->is_active]);
    }

    public function render()
    {
        return view('livewire.departments.department-index', [
            'departments' => Department::withCount('services')
                ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
                ->orderBy('name')
                ->paginate(15),
        ])->extends('layouts.app')->title('Departments');
    }
}
