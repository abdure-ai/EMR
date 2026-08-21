<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleCreate extends Component
{
    public string $name = '';

    /** @var array<int, string> */
    public array $selectedPermissions = [];

    public function mount(): void
    {
        Gate::authorize('create', Role::class);
    }

    public function save()
    {
        Gate::authorize('create', Role::class);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'selectedPermissions' => ['array'],
            'selectedPermissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = Role::create(['name' => $validated['name'], 'guard_name' => 'web']);
        $role->syncPermissions($validated['selectedPermissions']);

        session()->flash('status', "Role \"{$role->name}\" was created.");

        return $this->redirect(route('admin.roles.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.role-create', [
            'permissionGroups' => Permission::orderBy('name')->get()->groupBy(
                fn (Permission $permission) => explode('.', $permission->name)[0]
            ),
        ])->extends('layouts.app')->title('New Role');
    }
}
