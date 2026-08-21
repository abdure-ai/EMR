<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleEdit extends Component
{
    public Role $role;

    public string $name = '';

    /** @var array<int, string> */
    public array $selectedPermissions = [];

    public function mount(Role $role): void
    {
        Gate::authorize('update', $role);

        $this->role = $role;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('name')->all();
    }

    public function save()
    {
        Gate::authorize('update', $this->role);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,'.$this->role->id],
            'selectedPermissions' => ['array'],
            'selectedPermissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $this->role->update(['name' => $validated['name']]);
        $this->role->syncPermissions($validated['selectedPermissions']);

        session()->flash('status', "Role \"{$this->role->name}\" was updated.");

        return $this->redirect(route('admin.roles.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.role-edit', [
            'permissionGroups' => Permission::orderBy('name')->get()->groupBy(
                fn (Permission $permission) => explode('.', $permission->name)[0]
            ),
        ])->extends('layouts.app')->title('Edit Role');
    }
}
