<?php

namespace App\Observers;

use App\Models\Carrier;

class CarrierObserver
{
    /**
     * Handle the Carrier "created" event.
     */
    public function created(Carrier $carrier): void
    {
        //
    }

    /**
     * Handle the Carrier "updated" event.
     */
    public function updated(Carrier $carrier): void
    {
        //
    }

    /**
     * Handle the Carrier "deleted" event.
     */
    public function deleted(Carrier $carrier): void
    {
        //
    }

    /**
     * Handle the Carrier "restored" event.
     */
    public function restored(Carrier $carrier): void
    {
        //
    }

    /**
     * Handle the Carrier "force deleted" event.
     */
    public function forceDeleted(Carrier $carrier): void
    {
        //
    }
}
