<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Nova;

class UserObserver
{
    /**
     * Handle the User "creating" event.
     */
    public function creating(User $user): void
    {
        $authUser = Auth::user();

        if ($authUser && in_array($authUser->role, [User::SELLER_ROLE, User::USER_ROLE]) && $authUser->is_admin) {
            $user->parent_user_id = $authUser->id;
        }


        if (in_array($user->role, [User::USER_ROLE, User::SELLER_ROLE])) {
            $this->name($user);
        }
    }
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        if (in_array($user->role, [User::USER_ROLE])) {

            Wallet::create([
                'user_id' => $user->id,
                'currency' => 'USD',
                'used' => 0,
                'avaliable' => 0,
                'total' => 0
            ]);
        }
    }

    /**
     * Handle the User "updating" event.
     */
    public function updating(User $user): void
    {
        if (in_array($user->role, [User::USER_ROLE, User::SELLER_ROLE]) && ($user->isDirty('first_name') || $user->isDirty('last_name'))) {
            $this->name($user);
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    public function deleting(User $user): void
    {
       
    }
    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        if($user->role == User::SELLER_ROLE) {
            $user->numbers()->where('is_used', false)->delete();
            $user->sellerOrders()->delete();
        }
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }

    private function name(User $user)
    {

        $user->name = ucfirst($user->first_name) . ' ' . ucfirst($user->last_name);
    }
}
