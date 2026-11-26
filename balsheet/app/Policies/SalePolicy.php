<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;

class SalePolicy
{
    /**
     * Any authenticated user can view the sales history.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Any authenticated user can view a sale.
     */
    public function view(User $user, Sale $sale): bool
    {
        return true;
    }

    /**
     * Any authenticated user can create sales (staff + admin).
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Only administrators can cancel a sale (and reverse stock/debt).
     */
    public function cancel(User $user, Sale $sale): bool
    {
        return $user->isAdmin();
    }
}
