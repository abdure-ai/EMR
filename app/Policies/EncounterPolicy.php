<?php

namespace App\Policies;

use App\Models\Encounter;
use App\Models\User;

class EncounterPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('patients.medical.view');
    }

    public function view(User $user, Encounter $encounter): bool
    {
        return $user->can('patients.medical.view');
    }

    /**
     * Only the treating practitioner can fill in their own encounter, and
     * only while it's still a draft - once saved/finalized it's locked to
     * read-only for everyone, matching how a real chart note works.
     */
    public function update(User $user, Encounter $encounter): bool
    {
        if (! $user->can('patients.medical.update')) {
            return false;
        }

        if ($encounter->status !== 'draft') {
            return false;
        }

        if ($user->hasRole('Practitioner')) {
            return $encounter->practitioner_id === $user->id;
        }

        return true;
    }
}
