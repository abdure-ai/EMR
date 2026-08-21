<?php

namespace App\Policies;

use App\Models\QueueEntry;
use App\Models\User;

class QueueEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('queue.view');
    }

    public function view(User $user, QueueEntry $queueEntry): bool
    {
        return $user->can('queue.view');
    }

    public function create(User $user): bool
    {
        return $user->can('queue.manage');
    }

    /**
     * Reception/Clinic Manager coordinate the queue for every practitioner.
     * A Practitioner can only advance their own queue (start/complete) - not
     * reassign or manage someone else's patients.
     */
    public function update(User $user, QueueEntry $queueEntry): bool
    {
        if (! $user->can('queue.manage')) {
            return false;
        }

        if ($user->hasRole('Practitioner')) {
            return $queueEntry->practitioner_id === $user->id;
        }

        return true;
    }
}
