<?php

namespace App\Traits;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Adds automatic activity logging to any Eloquent model.
 *
 * Logs created / updated / deleted events. Only changed attributes
 * are recorded on updates. Empty logs (no actual diff) are discarded.
 *
 * Override getActivitylogOptions() in any model to customise behaviour.
 */
trait HasActivityLog
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        $logName = strtolower(class_basename($this));

        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName($logName)
            ->setDescriptionForEvent(
                fn (string $eventName) => ucfirst($logName).' was '.$eventName
            );
    }
}
