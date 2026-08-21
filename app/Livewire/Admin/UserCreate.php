<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class UserCreate extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $role = '';

    public function mount(): void
    {
        Gate::authorize('create', User::class);
    }

    public function save()
    {
        Gate::authorize('create', User::class);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        session()->flash('status', "{$user->name} was created and assigned the {$validated['role']} role.");

        return $this->redirect(route('admin.users.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.user-create', [
            'roles' => Role::pluck('name'),
        ])->extends('layouts.app')->title('New User');
    }
}
