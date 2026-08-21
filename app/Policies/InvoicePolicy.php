<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('billing.view');
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->can('billing.view');
    }

    /**
     * "Update" here means processing payment on a pending invoice - the only
     * write action a cashier takes against an invoice.
     */
    public function update(User $user, Invoice $invoice): bool
    {
        return $user->can('billing.process');
    }
}
