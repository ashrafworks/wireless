<?php

namespace App\Observers;

use App\Models\Area;

class AreaObserver
{
    /**
     * Handle the Area "created" event.
     */
    public function created(Area $area): void
    {
        //
    }

    /**
     * Handle the Area "updated" event.
     */
    public function updated(Area $area): void
    {
        //
    }

    /**
     * Handle the Area "deleted" event.
     */
    public function deleted(Area $area): void
    {
        //
    }

    /**
     * Handle the Area "restored" event.
     */
    public function restored(Area $area): void
    {
        //
    }

    /**
     * Handle the Area "force deleted" event.
     */
    public function forceDeleted(Area $area): void
    {
        //
    }
}
