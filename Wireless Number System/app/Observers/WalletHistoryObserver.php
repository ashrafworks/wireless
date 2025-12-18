<?php

namespace App\Observers;

use App\Models\WalletHistory;

class WalletHistoryObserver
{
    /**
     * Handle the WalletHistory "created" event.
     */
    public function created(WalletHistory $walletHistory): void
    {
        //
    }

    /**
     * Handle the WalletHistory "updated" event.
     */
    public function updated(WalletHistory $walletHistory): void
    {
        //
    }

    /**
     * Handle the WalletHistory "deleted" event.
     */
    public function deleted(WalletHistory $walletHistory): void
    {
        //
    }

    /**
     * Handle the WalletHistory "restored" event.
     */
    public function restored(WalletHistory $walletHistory): void
    {
        //
    }

    /**
     * Handle the WalletHistory "force deleted" event.
     */
    public function forceDeleted(WalletHistory $walletHistory): void
    {
        //
    }
}
