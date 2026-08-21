<?php

namespace App\Policies;

use App\Models\Medication;
use App\Models\User;

class MedicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('services.manage');
    }

    public function view(User $user, Medication $medication): bool
    {
        return $user->can('services.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('services.manage');
    }

    public function update(User $user, Medication $medication): bool
    {
        return $user->can('services.manage');
    }
}
