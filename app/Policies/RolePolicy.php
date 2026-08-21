<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.manage');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->can('users.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('users.manage');
    }

    public function update(User $user, Role $role): bool
    {
        // Super Admin is unrestricted by design (see Gate::before in
        // AppServiceProvider) - editing its permission list would be
        // meaningless and inviting someone to strip it down is a footgun.
        return $user->can('users.manage') && $role->name !== 'Super Admin';
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->can('users.manage') && $role->name !== 'Super Admin';
    }
}
