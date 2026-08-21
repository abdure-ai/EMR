<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;
use Spatie\Permission\Models\Permission;

class PermissionIndex extends Component
{
    #[Url(as: 'q', history: true)]
    public string $search = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Permission::class);
    }

    public function clearFilters(): void
    {
        $this->reset('search');
    }

    public function delete(int $permissionId): void
    {
        $permission = Permission::findOrFail($permissionId);

        Gate::authorize('delete', $permission);

        $permission->delete();

        session()->flash('status', "Permission \"{$permission->name}\" was deleted.");
    }

    public function render()
    {
        return view('livewire.admin.permission-index', [
            'permissionGroups' => Permission::withCount('roles')
                ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
                ->orderBy('name')
                ->get()
                ->groupBy(fn (Permission $permission) => explode('.', $permission->name)[0]),
        ])->extends('layouts.app')->title('Permissions');
    }
}
