<?php

namespace App\Observers;

use App\Models\Holiday;

class HolidayObserver
{
    /**
     * Handle the Holiday "created" event.
     *
     * This fires after a new holiday row has been saved to the database.
     * Add any side effects that must happen right after creation.
     */
    public function created(Holiday $holiday): void
    {
        // Example: clear a cached holiday list so the UI shows the new entry.
        // cache()->forget('active_holidays');
    }

    /**
     * Handle the Holiday "updated" event.
     *
     * This fires after a holiday row has been changed and saved.
     * Add any side effects that must happen when holiday details change.
     */
    public function updated(Holiday $holiday): void
    {
        // Example: if the holiday date changed, you could recalculate affected leave balances.
        // if ($holiday->wasChanged('date')) { ... }
    }

    /**
     * Handle the Holiday "deleting" event.
     *
     * This fires BEFORE the holiday is removed from the database.
     * Add any cascading effects here.
     */
    public function deleting(Holiday $holiday): void
    {
        // Add cascade logic here if deleting this holiday should affect related records.
    }

    /**
     * Handle the Holiday "deleted" event.
     *
     * This fires AFTER the holiday has been removed from the database.
     * Add any final cleanup actions here.
     */
    public function deleted(Holiday $holiday): void
    {
        // Example: clear any caches that held this holiday's data.
        // cache()->forget('active_holidays');
    }
}
