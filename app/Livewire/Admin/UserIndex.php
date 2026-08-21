<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class UserIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', User::class);
    }

    public function assignRole(int $userId, string $role): void
    {
        Gate::authorize('update', User::class);

        $user = User::findOrFail($userId);
        $user->syncRoles([$role]);
    }

    public function toggleActive(int $userId): void
    {
        Gate::authorize('update', User::class);

        $user = User::findOrFail($userId);

        if ($user->id === auth()->id()) {
            $this->addError('self', "You can't deactivate your own account.");

            return;
        }

        $user->update(['is_active' => ! $user->is_active]);
    }

    public function render()
    {
        return view('livewire.admin.user-index', [
            'users' => User::query()
                ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%"))
                ->with('roles')
                ->orderBy('name')
                ->paginate(15),
            'roles' => Role::pluck('name'),
        ])->extends('layouts.app')->title('Users & Roles');
    }
}
