<?php

namespace App\Policies;

use App\Models\Investigation;
use App\Models\User;

class InvestigationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('services.manage');
    }

    public function view(User $user, Investigation $investigation): bool
    {
        return $user->can('services.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('services.manage');
    }

    public function update(User $user, Investigation $investigation): bool
    {
        return $user->can('services.manage');
    }
}
