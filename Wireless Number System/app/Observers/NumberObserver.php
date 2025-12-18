<?php

namespace App\Observers;

use App\Models\Number;

class NumberObserver
{
    /**
     * Handle the Number "created" event.
     */
    public function created(Number $number): void
    {
        //
    }

    /**
     * Handle the Number "updated" event.
     */
    public function updated(Number $number): void
    {
        //
    }

    /**
     * Handle the Number "deleted" event.
     */
    public function deleted(Number $number): void
    {
        //
    }

    /**
     * Handle the Number "restored" event.
     */
    public function restored(Number $number): void
    {
        //
    }

    /**
     * Handle the Number "force deleted" event.
     */
    public function forceDeleted(Number $number): void
    {
        //
    }
}
