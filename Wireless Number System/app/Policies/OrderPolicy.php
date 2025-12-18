<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Order $order): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Order $order): bool
    {
        return in_array(request()->action, ['auto-assign-remaining-numbers','export-order-numbers','copy-order-number','copy-numbers']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Order $order): bool
    {
        return in_array($user->role, [User::NTS_ADMINISTRATOR_ROLE, User::SUPER_ADMINISTRATOR_ROLE]);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Order $order): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Order $order): bool
    {
        return false;
    }

    public function attachAnyNumber(User $user)
    {
        return false;
    }

    public function attachNumber(User $user)
    {
        return false;
    }

    public function detachAnyNumber(User $user)
    {
        return false;
    }

    public function detachNumber(User $user)
    {
        return false;
    }
}
