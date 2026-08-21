<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class RoleIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Role::class);
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

    public function delete(int $roleId): void
    {
        $role = Role::findOrFail($roleId);

        Gate::authorize('delete', $role);

        $role->delete();

        session()->flash('status', "Role \"{$role->name}\" was deleted.");
    }

    public function render()
    {
        return view('livewire.admin.role-index', [
            'roles' => Role::withCount(['users', 'permissions'])
                ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
                ->orderBy('name')
                ->paginate(15),
        ])->extends('layouts.app')->title('Roles');
    }
}
