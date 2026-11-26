<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    /**
     * Any authenticated user can view the customer list.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Any authenticated user can view a customer.
     */
    public function view(User $user, Customer $customer): bool
    {
        return true;
    }

    /**
     * Any authenticated user can create customers.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Any authenticated user can edit customers.
     */
    public function update(User $user, Customer $customer): bool
    {
        return true;
    }

    /**
     * Only administrators can delete customers.
     */
    public function delete(User $user, Customer $customer): bool
    {
        return $user->isAdmin();
    }
}
