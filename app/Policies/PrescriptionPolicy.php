<?php

namespace App\Policies;

use App\Models\Prescription;
use App\Models\User;

class PrescriptionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('prescriptions.view');
    }

    public function view(User $user, Prescription $prescription): bool
    {
        return $user->can('prescriptions.view');
    }

    /**
     * A prescription can only be dispensed once - already-dispensed or
     * cancelled prescriptions are locked, same spirit as a finalized
     * Encounter being read-only.
     */
    public function dispense(User $user, Prescription $prescription): bool
    {
        return $user->can('prescriptions.dispense') && $prescription->status === 'pending';
    }
}
