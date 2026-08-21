<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Spatie\Permission\Models\Permission;

class PermissionCreate extends Component
{
    public string $name = '';

    public function mount(): void
    {
        Gate::authorize('create', Permission::class);
    }

    public function save()
    {
        Gate::authorize('create', Permission::class);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name', 'regex:/^[a-z0-9]+(\.[a-z0-9]+)+$/'],
        ], [
            'name.regex' => 'Use dot notation, e.g. "inventory.view" (lowercase, no spaces).',
        ]);

        Permission::create(['name' => $validated['name'], 'guard_name' => 'web']);

        session()->flash('status', "Permission \"{$validated['name']}\" was created.");

        return $this->redirect(route('admin.permissions.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.permission-create')
            ->extends('layouts.app')->title('New Permission');
    }
}
