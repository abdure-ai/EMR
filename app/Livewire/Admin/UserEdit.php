<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class UserEdit extends Component
{
    public User $user;

    public string $name = '';

    public string $email = '';

    public string $role = '';

    public function mount(User $user): void
    {
        Gate::authorize('update', $user);

        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->roles->first()->name ?? '';
    }

    public function save()
    {
        Gate::authorize('update', $this->user);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$this->user->id],
            'role' => ['nullable', 'string', 'exists:roles,name'],
        ]);

        $this->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        $this->user->syncRoles(array_filter([$validated['role']]));

        session()->flash('status', "{$this->user->name} was updated.");

        return $this->redirect(route('admin.users.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.user-edit', [
            'roles' => Role::pluck('name'),
        ])->extends('layouts.app')->title('Edit User');
    }
}
