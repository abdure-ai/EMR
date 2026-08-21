<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PatientPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('patients.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Patient $patient): bool
    {
        if (! $user->can('patients.view')) {
            return false;
        }

        // A practitioner shouldn't be able to identify a patient the cashier
        // hasn't confirmed yet, even by guessing the URL directly. But once a
        // patient has actually been queued for care at least once, a later,
        // unrelated pending invoice (e.g. a next visit's fee) must not lock
        // the practitioner back out of a patient they're already treating.
        if ($user->hasRole('Practitioner')
            && ! $patient->queueEntries()->exists()
            && $patient->invoices()->where('status', 'pending')->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('patients.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Patient $patient): bool
    {
        return $user->can('patients.update');
    }

    /**
     * Determine whether the user can view the patient's clinical/medical record.
     */
    public function viewMedical(User $user, Patient $patient): bool
    {
        return $user->can('patients.medical.view');
    }

    /**
     * Determine whether the user can edit the patient's clinical/medical record.
     */
    public function updateMedical(User $user, Patient $patient): bool
    {
        return $user->can('patients.medical.update');
    }

    /**
     * Determine whether the user can delete (archive) the model.
     */
    public function delete(User $user, Patient $patient): bool
    {
        return $user->can('patients.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Patient $patient): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Patient $patient): bool
    {
        return false;
    }
}
