<?php

namespace App\Policies;

use App\Models\InventoryBatch;
use App\Models\User;

class InventoryBatchPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('inventory.view');
    }

    public function view(User $user, InventoryBatch $batch): bool
    {
        return $user->can('inventory.view');
    }

    public function create(User $user): bool
    {
        return $user->can('inventory.manage');
    }

    public function update(User $user, InventoryBatch $batch): bool
    {
        return $user->can('inventory.manage');
    }
}
